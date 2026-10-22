import asyncio
import json
import os
import subprocess
import time
import urllib.request
import websockets

def main():
    chrome_proc = subprocess.Popen([
        '/usr/bin/google-chrome',
        '--headless=new',
        '--remote-debugging-port=9222',
        '--disable-gpu',
        '--disable-extensions',
        '--no-sandbox',
        '--window-size=1400,900',
        'about:blank'
    ])
    
    try:
        time.sleep(1)
        req = urllib.request.urlopen('http://127.0.0.1:9222/json')
        targets = json.loads(req.read().decode('utf-8'))
        page_targets = [t for t in targets if t.get('type') == 'page']
        ws_url = page_targets[0]['webSocketDebuggerUrl']
        
        async def run():
            async with websockets.connect(ws_url) as ws:
                msg_id = 0
                async def send(method, params=None):
                    nonlocal msg_id
                    msg_id += 1
                    payload = {'id': msg_id, 'method': method}
                    if params:
                        payload['params'] = params
                    await ws.send(json.dumps(payload))
                    while True:
                        res = json.loads(await ws.recv())
                        if res.get('id') == msg_id:
                            return res

                await send('Page.enable')
                await send('Runtime.enable')

                print("Navigating to test login...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/_test_login'})
                await asyncio.sleep(2)

                # 1. Click dropdown on Row 0
                print("1. Opening dropdown 0...")
                await send('Runtime.evaluate', {
                    'expression': 'document.querySelectorAll("button[id^=\\"select-items-\\"]")[0].click()'
                })
                await asyncio.sleep(0.3)

                # 2. Type "logitech" in the search box
                print("2. Searching 'logitech'...")
                search_res = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const input = document.querySelector('table input[placeholder*="Cari"]');
                        input.value = 'logitech';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    })()
                    """
                })
                await asyncio.sleep(0.3)

                # Check filtered options count
                filter_check = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const btns = Array.from(document.querySelectorAll('table div[x-show="open"] button'))
                            .filter(b => b.innerText.trim() && !b.innerText.includes('×'));
                        return btns.map(b => b.innerText.trim());
                    })()
                    """,
                    'returnByValue': True
                })
                print("Filtered options for 'logitech':", filter_check['result']['result']['value'])

                # 3. Click the filtered option to select it
                print("3. Selecting option...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const opt = Array.from(document.querySelectorAll('table div[x-show="open"] button'))
                            .find(b => b.innerText.includes('Logitech'));
                        if (opt) opt.click();
                    })()
                    """
                })
                await asyncio.sleep(0.5)

                # Check if trigger label updated and dropdown closed
                select_check = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const trigger = document.querySelectorAll("button[id^=\\"select-items-\\"]")[0];
                        const dropdown = document.querySelector('table div[x-show="open"]');
                        const isHidden = dropdown ? dropdown.style.display === 'none' : true;
                        return {
                            triggerLabel: trigger.innerText.trim(),
                            dropdownClosed: isHidden
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("After selection:", json.dumps(select_check['result']['result']['value'], indent=2))

                # Take screenshot after selection
                snap = await send('Page.captureScreenshot', {'format': 'png'})
                with open('/home/bobby/.gemini/antigravity/brain/50a68c4e-f44c-4a1f-9c44-b564fe18ce48/scratch/after_selection.png', 'wb') as f:
                    import base64
                    f.write(base64.b64decode(snap['result']['data']))
                print("Screenshot saved: scratch/after_selection.png")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()
        chrome_proc.wait()

if __name__ == '__main__':
    main()

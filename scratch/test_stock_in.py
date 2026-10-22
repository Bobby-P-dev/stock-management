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
        print("Connected to:", ws_url)
        
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
                await send('DOM.enable')

                print("Navigating to test login...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/_test_login'})
                await asyncio.sleep(2)

                res = await send('Runtime.evaluate', {'expression': 'window.location.href'})
                print("Current URL:", res.get('result', {}).get('result', {}).get('value'))

                # Click the first product searchable select trigger button
                print("Clicking first product searchable select trigger...")
                click_res = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const triggers = document.querySelectorAll('button[id^="select-items-"]');
                        if (triggers.length > 0) {
                            triggers[0].click();
                            return { success: true, id: triggers[0].id };
                        }
                        return { success: false, count: triggers.length };
                    })()
                    """,
                    'returnByValue': True
                })
                print("Click result:", click_res.get('result', {}).get('result', {}).get('value'))
                await asyncio.sleep(0.5)

                # Inspect how many options exist in the DOM and their visibility
                eval_res = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const openDropdown = document.querySelector('div[x-show="open"]');
                        const optionButtons = Array.from(document.querySelectorAll('div[x-show="open"] button')).map(b => b.innerText.trim());
                        let rect = null;
                        if (openDropdown) {
                            rect = openDropdown.getBoundingClientRect();
                        }
                        const alpineData = Array.from(document.querySelectorAll('div[x-data]')).filter(d => d._x_dataStack).map(d => {
                            const stack = d._x_dataStack[0];
                            if (stack && stack.options) {
                                return {
                                    optionsLength: stack.options.length,
                                    limit: stack.limit,
                                    filteredLength: stack.filteredOptions ? stack.filteredOptions.length : null,
                                    open: stack.open
                                };
                            }
                            return null;
                        }).filter(Boolean);

                        return {
                            optionButtons: optionButtons,
                            dropdownRect: rect,
                            alpineSelectComponents: alpineData
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("Options info:\n", json.dumps(eval_res.get('result', {}).get('result', {}).get('value'), indent=2))

                # Screenshot initial dropdown
                snap1 = await send('Page.captureScreenshot', {'format': 'png'})
                with open('/home/bobby/.gemini/antigravity/brain/50a68c4e-f44c-4a1f-9c44-b564fe18ce48/scratch/dropdown_initial.png', 'wb') as f:
                    import base64
                    f.write(base64.b64decode(snap1['result']['data']))
                print("Screenshot 1 saved: scratch/dropdown_initial.png")

                # Add 3 rows
                print("Adding 3 rows...")
                for i in range(3):
                    await send('Runtime.evaluate', {
                        'expression': """
                        (() => {
                            const addBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Tambah Baris Barang'));
                            if (addBtn) addBtn.click();
                        })()
                        """
                    })
                    await asyncio.sleep(0.6)

                # Open dropdown on the last row
                print("Clicking dropdown on the last row...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const triggers = document.querySelectorAll('button[id^="select-items-"]');
                        if (triggers.length > 0) {
                            triggers[triggers.length - 1].click();
                        }
                    })()
                    """
                })
                await asyncio.sleep(0.5)

                # Screenshot last row dropdown
                snap2 = await send('Page.captureScreenshot', {'format': 'png'})
                with open('/home/bobby/.gemini/antigravity/brain/50a68c4e-f44c-4a1f-9c44-b564fe18ce48/scratch/dropdown_last_row.png', 'wb') as f:
                    import base64
                    f.write(base64.b64decode(snap2['result']['data']))
                print("Screenshot 2 saved: scratch/dropdown_last_row.png")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()
        chrome_proc.wait()

if __name__ == '__main__':
    main()

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

                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/_test_login'})
                await asyncio.sleep(2)

                # Click dropdown 0
                await send('Runtime.evaluate', {
                    'expression': 'document.querySelectorAll("button[id^=\\"select-items-\\"]")[0].click()'
                })
                await asyncio.sleep(0.5)

                diag = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const tableDiv = document.querySelector('table').parentElement;
                        const cardDiv = tableDiv.parentElement;
                        const dropdown = document.querySelector('table div[x-show="open"]');
                        const optionsList = dropdown ? dropdown.querySelector('.max-h-56') : null;
                        
                        return {
                            tableDiv: {
                                classes: tableDiv.className,
                                computedOverflowX: getComputedStyle(tableDiv).overflowX,
                                computedOverflowY: getComputedStyle(tableDiv).overflowY,
                                clientHeight: tableDiv.clientHeight,
                                scrollHeight: tableDiv.scrollHeight,
                                hasVerticalScrollbar: tableDiv.scrollHeight > tableDiv.clientHeight
                            },
                            cardDiv: {
                                classes: cardDiv.className,
                                computedOverflowX: getComputedStyle(cardDiv).overflowX,
                                computedOverflowY: getComputedStyle(cardDiv).overflowY
                            },
                            dropdown: dropdown ? {
                                rect: dropdown.getBoundingClientRect(),
                                offsetTop: dropdown.offsetTop,
                                offsetHeight: dropdown.offsetHeight
                            } : null,
                            optionsList: optionsList ? {
                                scrollHeight: optionsList.scrollHeight,
                                clientHeight: optionsList.clientHeight,
                                childrenCount: optionsList.children.length
                            } : null
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("DIAGNOSTICS:\n", json.dumps(diag['result']['result']['value'], indent=2))

        asyncio.run(run())
    finally:
        chrome_proc.terminate()
        chrome_proc.wait()

if __name__ == '__main__':
    main()

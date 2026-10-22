import asyncio
import json
import os
import subprocess
import time
import urllib.request
import websockets
import base64

def main():
    chrome_proc = subprocess.Popen([
        '/usr/bin/google-chrome',
        '--headless=new',
        '--remote-debugging-port=9222',
        '--disable-gpu',
        '--disable-extensions',
        '--no-sandbox',
        '--window-size=375,812',
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

                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/login'})
                await asyncio.sleep(1.5)

                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const emailInput = document.querySelector('input[type="email"]') || document.querySelector('input[name="email"]');
                        const passInput = document.querySelector('input[type="password"]') || document.querySelector('input[name="password"]');
                        if (emailInput) {
                            emailInput.value = 'admin@stock.com';
                            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        if (passInput) {
                            passInput.value = 'password';
                            passInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                        const form = document.querySelector('form');
                        if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    })()
                    """
                })
                await asyncio.sleep(2)

                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions/out'})
                await asyncio.sleep(2)

                shot = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/stock_out_mobile.png', 'wb') as f:
                    f.write(base64.b64decode(shot['result']['data']))
                print("Saved scratch/stock_out_mobile.png")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()

if __name__ == '__main__':
    main()

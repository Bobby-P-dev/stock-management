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
        '--window-size=1200,1600',
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

                # Login
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/login'})
                await asyncio.sleep(1.5)
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const emailInput = document.querySelector('input[type="email"]') || document.querySelector('input[name="email"]');
                        const passInput = document.querySelector('input[type="password"]') || document.querySelector('input[name="password"]');
                        if (emailInput) emailInput.value = 'admin@stock.com';
                        if (passInput) passInput.value = 'password';
                        emailInput?.dispatchEvent(new Event('input', { bubbles: true }));
                        passInput?.dispatchEvent(new Event('input', { bubbles: true }));
                        const form = document.querySelector('form');
                        if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                    })()
                    """
                })
                await asyncio.sleep(2)

                # Navigate to detail
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions/1'})
                await asyncio.sleep(2)

                # Emulate print media
                print("Setting print emulation...")
                await send('Emulation.setEmulatedMedia', {'media': 'print'})
                await asyncio.sleep(1)

                shot_print = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/transaction_detail_print.png', 'wb') as f:
                    f.write(base64.b64decode(shot_print['result']['data']))
                print("Saved scratch/transaction_detail_print.png")

                # Generate PDF using printToPDF
                pdf_res = await send('Page.printToPDF', {
                    'paperWidth': 8.27,  # A4 inches
                    'paperHeight': 11.69,
                    'marginTop': 0.4,
                    'marginBottom': 0.4,
                    'marginLeft': 0.4,
                    'marginRight': 0.4,
                    'printBackground': True
                })
                with open('/var/www/stock-management/scratch/transaction_detail.pdf', 'wb') as f:
                    f.write(base64.b64decode(pdf_res['result']['data']))
                print("Saved scratch/transaction_detail.pdf")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()

if __name__ == '__main__':
    main()

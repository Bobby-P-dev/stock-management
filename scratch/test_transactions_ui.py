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

                # 1. Riwayat Mutasi List
                print("1. Navigating to Transactions list...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions'})
                await asyncio.sleep(2)

                list_info = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const table = document.querySelector('table');
                        const filterCard = document.querySelector('form') || document.querySelector('.bg-white.border');
                        const rows = document.querySelectorAll('tbody tr');
                        return {
                            tableWidth: table ? table.offsetWidth : 0,
                            rowsCount: rows.length,
                            windowWidth: window.innerWidth
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("List Info:", list_info['result']['result']['value'])

                shot_list = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/transactions_list.png', 'wb') as f:
                    f.write(base64.b64decode(shot_list['result']['data']))
                print("Saved scratch/transactions_list.png")

                # 2. Filter search test
                print("2. Testing search filter...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const input = document.querySelector('input[placeholder*="Cari"]');
                        if (input) {
                            input.value = 'SIN';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    })()
                    """
                })
                await asyncio.sleep(1)

                shot_filter = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/transactions_filtered.png', 'wb') as f:
                    f.write(base64.b64decode(shot_filter['result']['data']))
                print("Saved scratch/transactions_filtered.png")

                # Reset search
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const resetBtn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.includes('Reset Filter'));
                        if (resetBtn) resetBtn.click();
                    })()
                    """
                })
                await asyncio.sleep(1)

                # 3. Transaction Detail Page
                print("3. Navigating to Transaction Detail (ID: 1)...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions/1'})
                await asyncio.sleep(2)

                detail_info = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const docCard = document.querySelector('div.bg-white.border');
                        const itemsTable = document.querySelector('table');
                        return {
                            docCardWidth: docCard ? docCard.offsetWidth : 0,
                            itemsTableWidth: itemsTable ? itemsTable.offsetWidth : 0,
                            itemsCount: document.querySelectorAll('tbody tr').length
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("Detail Info:", detail_info['result']['result']['value'])

                shot_detail = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/transaction_detail.png', 'wb') as f:
                    f.write(base64.b64decode(shot_detail['result']['data']))
                print("Saved scratch/transaction_detail.png")

                # 4. Mobile Viewport test for List
                print("4. Testing mobile viewport...")
                await send('Emulation.setDeviceMetricsOverride', {
                    'width': 375,
                    'height': 812,
                    'deviceScaleFactor': 2,
                    'mobile': True
                })
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions'})
                await asyncio.sleep(2)

                shot_mobile = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/transactions_mobile.png', 'wb') as f:
                    f.write(base64.b64decode(shot_mobile['result']['data']))
                print("Saved scratch/transactions_mobile.png")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()

if __name__ == '__main__':
    main()

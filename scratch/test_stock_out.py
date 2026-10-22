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

                print("Navigating to login...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/login'})
                await asyncio.sleep(1.5)

                # Fill login credentials
                print("Logging in as admin@stock.com...")
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

                print("Navigating to Stock OUT page...")
                await send('Page.navigate', {'url': 'http://127.0.0.1:8000/transactions/out'})
                await asyncio.sleep(2)

                # Capture full page width and container width info
                width_info = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const pageWrapper = document.querySelector('div.space-y-6.min-w-0');
                        const table = document.querySelector('table');
                        const card1 = document.querySelector('h2').closest('.bg-white');
                        return {
                            hasFullWidthClass: !!pageWrapper,
                            tableWidth: table ? table.offsetWidth : 0,
                            cardWidth: card1 ? card1.offsetWidth : 0,
                            windowWidth: window.innerWidth
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("Layout Width Info:", width_info['result']['result']['value'])

                # Screenshot 1: Stock OUT initial layout
                shot1 = await send('Page.captureScreenshot')
                import base64
                with open('/var/www/stock-management/scratch/stock_out_initial.png', 'wb') as f:
                    f.write(base64.b64decode(shot1['result']['data']))
                print("Saved scratch/stock_out_initial.png")

                # Click dropdown on Row 0
                print("Opening product dropdown on row 0...")
                await send('Runtime.evaluate', {
                    'expression': 'document.querySelectorAll("button[id^=\\"select-items-\\"]")[0].click()'
                })
                await asyncio.sleep(0.5)

                # Check options in dropdown
                options_info = await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const opts = Array.from(document.querySelectorAll('table div[x-show="open"] button'))
                            .filter(b => b.innerText.trim() && !b.innerText.includes('×'));
                        const tableContainer = document.querySelector('table').parentElement;
                        return {
                            optionsCount: opts.length,
                            optionsText: opts.map(b => b.innerText.trim()),
                            containerHasScrollbar: tableContainer.scrollHeight > tableContainer.clientHeight && getComputedStyle(tableContainer).overflowY !== 'visible'
                        };
                    })()
                    """,
                    'returnByValue': True
                })
                print("Dropdown Options Info:", options_info['result']['result']['value'])

                # Screenshot 2: Dropdown opened
                shot2 = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/stock_out_dropdown.png', 'wb') as f:
                    f.write(base64.b64decode(shot2['result']['data']))
                print("Saved scratch/stock_out_dropdown.png")

                # Test Search filter: type "logitech"
                print("Searching for 'logitech'...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const input = document.querySelector('table input[placeholder*="Cari"]');
                        input.value = 'logitech';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    })()
                    """
                })
                await asyncio.sleep(0.3)

                # Select Logitech
                print("Selecting Logitech product...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const opt = Array.from(document.querySelectorAll('table div[x-show="open"] button'))
                            .find(b => b.innerText.toLowerCase().includes('logitech'));
                        if (opt) opt.click();
                    })()
                    """
                })
                await asyncio.sleep(0.5)

                # Check trigger label
                trigger_label = await send('Runtime.evaluate', {
                    'expression': 'document.querySelectorAll("button[id^=\\"select-items-\\"]")[0].innerText.trim()',
                    'returnByValue': True
                })
                print("Trigger label after selection:", trigger_label['result']['result']['value'])

                # Add 3 rows
                print("Adding 3 rows...")
                for i in range(3):
                    await send('Runtime.evaluate', {
                        'expression': """
                        (() => {
                            const btn = Array.from(document.querySelectorAll('button'))
                                .find(b => b.innerText.includes('Tambah Baris Barang'));
                            if (btn) btn.click();
                        })()
                        """
                    })
                    await asyncio.sleep(0.6)

                rows_count = await send('Runtime.evaluate', {
                    'expression': 'document.querySelectorAll("table tbody tr").length',
                    'returnByValue': True
                })
                print(f"Total rows in table: {rows_count['result']['result']['value']}")

                # Open dropdown on row 4 (last row)
                print("Opening dropdown on last row...")
                await send('Runtime.evaluate', {
                    'expression': """
                    (() => {
                        const triggers = document.querySelectorAll("button[id^=\\"select-items-\\"]");
                        triggers[triggers.length - 1].click();
                    })()
                    """
                })
                await asyncio.sleep(0.5)

                # Screenshot 3: Multi-row with last row dropdown open
                shot3 = await send('Page.captureScreenshot')
                with open('/var/www/stock-management/scratch/stock_out_multi_rows.png', 'wb') as f:
                    f.write(base64.b64decode(shot3['result']['data']))
                print("Saved scratch/stock_out_multi_rows.png")

        asyncio.run(run())
    finally:
        chrome_proc.terminate()

if __name__ == '__main__':
    main()

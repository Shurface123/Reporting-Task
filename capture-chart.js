const puppeteer = require('puppeteer');

async function captureChart() {
    const htmlFile = process.argv[2];
    const pngFile = process.argv[3];

    if (!htmlFile || !pngFile) {
        console.error('Usage: node capture-chart.js <html-file> <png-file>');
        process.exit(1);
    }

    try {
        const browser = await puppeteer.launch();
        const page = await browser.newPage();
        
        // Set viewport to match chart container size
        await page.setViewport({
            width: 800,
            height: 400,
            deviceScaleFactor: 2 // For better quality
        });

        // Load the HTML file
        await page.goto('file://' + htmlFile);
        
        // Wait for Chart.js to render
        await page.waitForFunction(() => {
            const canvas = document.querySelector('canvas');
            return canvas && canvas.toDataURL() !== 'data:,';
        }, { timeout: 5000 });

        // Take screenshot
        await page.screenshot({
            path: pngFile,
            clip: {
                x: 0,
                y: 0,
                width: 800,
                height: 400
            }
        });

        await browser.close();
        console.log('Chart captured successfully');
        process.exit(0);
    } catch (error) {
        console.error('Error capturing chart:', error);
        process.exit(1);
    }
}

captureChart();

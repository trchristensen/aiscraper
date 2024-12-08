# AI Web Scraper API

A flexible web scraping API that uses AI to extract structured data from any webpage.

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/ai-web-scraper.git
cd ai-web-scraper
```

2. Configure your OpenAI API key:
   - Open `api/retrieve/AIScraper.php`
   - Replace the placeholder API key with your own:
```php
$this->openai_key = 'your-openai-api-key';
```

3. Set up your web server (Apache/Nginx) to point to the project directory

## Usage

Make a POST request to `/api/retrieve/` with the following JSON body:

```json
{
    "api_key": "test-key-123",
    "webpage_url": "https://example.com/product",
    "api_method_name": "getItemDetails",
    "api_response_structure": {
        "item_name": "<the item name>",
        "item_price": "<the item price>",
        "item_image": "<the absolute URL of the first item image>"
    }
}
```

### Parameters

- `api_key`: Your API authentication key (default test key: "test-key-123")
- `webpage_url`: The URL of the webpage to scrape
- `api_method_name`: Name of the extraction method (used for AI context)
- `api_response_structure`: JSON object defining the structure of data to extract
- `verbose` (optional): Set to `true` to get additional debug information

### Example Response

```json
{
    "response": {
        "item_name": "Example Product",
        "item_price": "$99.99",
        "item_image": "https://example.com/image.jpg"
    }
}
```

### Error Response

```json
{
    "error": true,
    "reason": "Error message here"
}
```

## Features

- Generic web scraping - works with any webpage
- AI-powered extraction using OpenAI's GPT models
- Flexible response structure
- Built-in HTML cleaning and preprocessing
- Error handling and logging
- Rate limit handling
- Verbose mode for debugging

## Requirements

- PHP 7.4 or higher
- OpenAI API key
- cURL extension enabled
- Web server (Apache/Nginx)

## Security Notes

- Store your OpenAI API key securely (preferably in environment variables)
- Implement proper API key validation for production use
- Consider implementing rate limiting for your API endpoints
- Monitor your OpenAI API usage and costs

## License

This project is licensed under the MIT License - see below for details:

```
MIT License

Copyright (c) 2024 Todd Christensen

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
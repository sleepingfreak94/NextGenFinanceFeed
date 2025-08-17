<?php

require_once __DIR__ . '/../config.php';


function fetchNifty50Companies(): array {
    $url = "https://en.wikipedia.org/wiki/NIFTY_50";

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 ' .
        '(KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        // Fallback static data if fetch fails
        return [
            ['name' => 'Tata Consultancy Services', 'ticker' => 'TCS', 'index_name' => 'Nifty 50'],
            ['name' => 'Reliance Industries', 'ticker' => 'RELIANCE', 'index_name' => 'Nifty 50'],
            ['name' => 'HDFC Bank', 'ticker' => 'HDFCBANK', 'index_name' => 'Nifty 50'],
        ];
    }

    // Load HTML into DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);  // Suppress HTML parsing warnings
    $dom->loadHTML($html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);

    // Query all wikitable sortable tables
    $tables = $xpath->query("//table[contains(@class,'wikitable')]");

    $companies = [];

    foreach ($tables as $table) {
        // Check if table has at least 2 header columns
        $headerCells = $xpath->query(".//th", $table);
        if ($headerCells->length < 2) continue;

        $rows = $xpath->query(".//tr", $table);

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;  // skip header row

            $cells = $xpath->query(".//td", $row);
            if ($cells->length < 2) continue;

            // Column 0 is company name, column 1 is ticker symbol
            $rawName = trim($cells->item(0)->textContent);
            $rawTicker = trim($cells->item(1)->textContent);

            // Remove footnote references like [1], [a]
            $name = preg_replace('/\[[^\]]*\]/', '', $rawName);
            $ticker = preg_replace('/\[[^\]]*\]/', '', $rawTicker);

            $name = trim($name);
            $ticker = trim($ticker);

            // Validate non-empty ticker and name before adding
            if ($ticker !== '' && $name !== '') {
                $companies[] = [
                    'name' => $name,
                    'ticker' => $ticker,
                    'index_name' => 'Nifty 50'
                ];
            }
        }

        // We've found and processed the first suitable table, break out of loop
        if (count($companies) > 0) break;
    }

    // Return fallback static data if no companies found (optional)
    if (empty($companies)) {
        $companies = [
            ['name' => 'Tata Consultancy Services', 'ticker' => 'TCS', 'index_name' => 'Nifty 50'],
            ['name' => 'Reliance Industries', 'ticker' => 'RELIANCE', 'index_name' => 'Nifty 50'],
            ['name' => 'HDFC Bank', 'ticker' => 'HDFCBANK', 'index_name' => 'Nifty 50'],
        ];
    }

    return $companies;
}




function fetchSP500Companies(): array {
    // Fetch S&P 500 list from Wikipedia
    $url = "https://en.wikipedia.org/wiki/List_of_S%26P_500_companies";

    $html = @file_get_contents($url);
    if (!$html) {
        // Fallback dummy data
        return [
            ['name' => 'Apple Inc.', 'ticker' => 'AAPL', 'index_name' => 'S&P 500'],
            ['name' => 'Microsoft Corporation', 'ticker' => 'MSFT', 'index_name' => 'S&P 500'],
            ['name' => 'Alphabet Inc.', 'ticker' => 'GOOGL', 'index_name' => 'S&P 500'],
        ];
    }

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($doc);
    // The first table with class 'wikitable sortable' is the S&P 500 list
    $tables = $xpath->query("//table[contains(@class, 'wikitable') and contains(@class, 'sortable')]");
    $rows = [];
    if ($tables->length > 0) {
        $table = $tables->item(0);
        $rows = $table->getElementsByTagName("tr");
    }

    $companies = [];
    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // Skip header row
        $cells = $row->getElementsByTagName("td");
        if ($cells->length < 2) continue;

        // Column 1: Ticker symbol
        $ticker = trim($cells->item(0)->textContent);
        // Column 2: Security (company name)
        $name = trim($cells->item(1)->textContent);

        $companies[] = ['name' => $name, 'ticker' => $ticker, 'index_name' => 'S&P 500'];
    }

    return $companies;
}

function syncCompanies(array $companies, mysqli $conn) {
    $indexName = $companies[0]['index_name'] ?? '';
    if (!$indexName) return;

    // Fetch existing companies from DB for this index
    $existing = [];
    $stmt = $conn->prepare("SELECT id, ticker, name, is_active FROM companies WHERE index_name = ?");
    $stmt->bind_param("s", $indexName);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existing[$row['ticker']] = $row;
    }
    $stmt->close();

    $tickersInNewList = [];

    foreach ($companies as $company) {
        $ticker = $company['ticker'];
        $name = $company['name'];
        $tickersInNewList[] = $ticker;

        if (isset($existing[$ticker])) {
            // Reactivate if previously inactive
            if ($existing[$ticker]['is_active'] == 0) {
                $stmt = $conn->prepare("UPDATE companies SET is_active = 1 WHERE id = ?");
                $stmt->bind_param("i", $existing[$ticker]['id']);
                $stmt->execute();
                $stmt->close();
                echo "Reactivated company $ticker\n";
            }
            // Update name if changed
            if ($existing[$ticker]['name'] !== $name) {
                $stmt = $conn->prepare("UPDATE companies SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $existing[$ticker]['id']);
                $stmt->execute();
                $stmt->close();
                echo "Updated company name for $ticker\n";
            }
        } else {
            // Insert new active company
            $stmt = $conn->prepare("INSERT INTO companies (name, ticker, index_name, is_active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $name, $ticker, $indexName);
            $stmt->execute();
            $stmt->close();
            echo "Inserted new company: $name ($ticker)\n";
        }
    }

    // Set companies NOT in the new list to inactive

    if (empty($tickersInNewList)) {
        // If no tickers, set all in this index inactive
        $sql = "UPDATE companies SET is_active = 0 WHERE index_name = '" . $conn->real_escape_string($indexName) . "'";
        if (!$conn->query($sql)) {
            echo "Error setting inactive companies: " . $conn->error . "\n";
        }
    } else {
        // Safely escape each ticker and build the IN list string
        $tickersEscaped = array_map(fn($ticker) => "'" . $conn->real_escape_string($ticker) . "'", $tickersInNewList);
        $tickersStr = implode(',', $tickersEscaped);
        $sql = "UPDATE companies 
                SET is_active = 0 
                WHERE index_name = '" . $conn->real_escape_string($indexName) . "' 
                AND ticker NOT IN ($tickersStr)";
        if (!$conn->query($sql)) {
            echo "Error setting inactive companies: " . $conn->error . "\n";
        }
    }

    echo "Sync completed for $indexName.\n";
}

// function fetchGoogleNews(string $query, string $apiKey, string $cseId, int $maxResults = 5): array {
//     $params = [
//         'q'     => $query . ' after:' . date('Y-m-d', strtotime('-14 days')), // Last 2 weeks
//         'key'   => $apiKey,
//         'cx'    => $cseId,
//         'num'   => $maxResults,
//     ];

//     $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query($params);
//     echo "API request: $url\n";

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     $response = curl_exec($ch);
//     curl_close($ch);

//     $data = json_decode($response, true);

//     if (!isset($data['items']) || !is_array($data['items'])) {
//         echo "Google News API returned no articles or invalid format.\n";
//         return [];
//     }

//     $newsItems = [];
//     foreach ($data['items'] as $item) {
//         $headline = $item['title'] ?? '';
//         $url = $item['link'] ?? '';
//         $summary = $item['snippet'] ?? '';
//         $source = parse_url($url, PHP_URL_HOST) ?? 'Unknown';
//         $publishedAt = date('Y-m-d H:i:s');

//         if ($headline && $url) {
//             $newsItems[] = [
//                 'headline'     => $headline,
//                 'url'          => $url,
//                 'summary'      => $summary,
//                 'source'       => $source,
//                 'published_at' => $publishedAt,
//             ];
//         }
//     }

//     return $newsItems;
// }



function fetchBraveNews($company, $apiKey, $isFallback = false) {
    $query = $isFallback
        ? "{$company} latest financial news"
        : "{$company} news site:moneycontrol.com/news OR site:economictimes.indiatimes.com/news OR site:livemint.com/news OR site:business-standard.com/article OR site:reuters.com/article OR site:bloomberg.com/news OR site:ndtv.com/news";

    $url = "https://api.search.brave.com/res/v1/web/search?q=" . urlencode($query) . "&country=in&safesearch=off";

    $headers = [
        "Accept: application/json",
        "X-Subscription-Token: $apiKey"
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    $items = $json['web']['results'] ?? [];

    $articles = [];

    foreach ($items as $item) {
        $rawUrl = $item['url'] ?? '';
        $title = $item['title'] ?? '';
        $description = $item['description'] ?? '';

        echo "Raw URL: {$rawUrl}" . PHP_EOL;

        // Skip if invalid or obviously not an article
        if (
            empty($title) ||
            empty($rawUrl) ||
            !filter_var($rawUrl, FILTER_VALIDATE_URL) ||
            !isLikelyArticle($rawUrl, $title, $description)
        ) {
            echo "Skipping non-article URL.\n";
            continue;
        }

        $articles[] = [
            'headline'     => $title,
            'url'          => $rawUrl,
            'summary'      => $description,
            'source'       => parse_url($rawUrl, PHP_URL_HOST),
            'published_at' => date('Y-m-d H:i:s')
        ];
    }

    return $articles;
}

function isLikelyArticle($url, $title = '', $description = '') {
    $url = strtolower($url);
    $title = strtolower($title);
    $description = strtolower($description);
    $path = parse_url($url, PHP_URL_PATH);

    // Reject if URL contains finance profile or share-price noise
    $nonArticlePatterns = [
        'stockpricequote', 'profile', 'quote', 'market-stats', 'stocksupdate',
        'companyid', 'symbol=', 'investor', 'about', 'stock', 'finance',
        'equity', 'get-quotes', 'bids-offers', 'share-price', 'tags',
        'consolidated', 'company', 'stocks'
    ];
    foreach ($nonArticlePatterns as $bad) {
        if (str_contains($url, $bad) || str_contains($title, $bad)) {
            return false;
        }
    }

    // Must contain a slug path or article keywords
    $mustHave = ['news', 'article', 'headline', 'breaking', 'earnings', 'launches', 'update'];
    foreach ($mustHave as $must) {
        if (str_contains($url, $must) || str_contains($description, $must)) {
            return true;
        }
    }

    // Require URL path to have slug-like content
    return preg_match('#/[a-z0-9\-]{8,}#', $path);
}






// function summarizeWithAzureAI(string $text, string $endpoint, string $apiKey): ?string {
//     $text = substr($text, 0, 4000); // truncate input to avoid Azure limits

//     $url = rtrim($endpoint, "/") . "/language/:analyze-text?api-version=2023-04-01-preview";

//     $body = json_encode([
//         'kind' => 'AbstractiveSummarization',
//         'analysisInput' => [
//             'documents' => [
//                 [
//                     'id' => '1',
//                     'language' => 'en',
//                     'text' => $text,
//                 ]
//             ]
//         ],
//         'parameters' => [
//             'sentenceCount' => 3
//         ]
//     ]);

//     $headers = [
//         "Ocp-Apim-Subscription-Key: $apiKey",
//         "Content-Type: application/json",
//         "Accept: application/json"
//     ];

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 30);

//     $response = curl_exec($ch);

//     if (curl_errno($ch)) {
//         error_log("Azure API cURL error: " . curl_error($ch));
//         curl_close($ch);
//         return null;
//     }

//     $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     curl_close($ch);

//     if ($statusCode !== 200) {
//         error_log("Azure API returned status code $statusCode: $response");
//         return null;
//     }

//     $data = json_decode($response, true);

//     return $data['results']['documents'][0]['summaries'][0]['text'] ?? null;
// }

function summarizeWithHuggingFace(string $text, string $apiToken): ?string {
    $url = "https://api-inference.huggingface.co/models/facebook/bart-large-cnn";
    $headers = [
        "Authorization: Bearer $apiToken",
        "Content-Type: application/json"
    ];

    // Truncate long text to avoid model input limit (max: ~1024 tokens)
    $body = json_encode(["inputs" => mb_substr($text, 0, 2000)]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("Hugging Face API cURL error: " . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($statusCode !== 200) {
        error_log("Hugging Face API returned status code $statusCode: $response");
        return null;
    }

    $data = json_decode($response, true);
    return $data[0]['summary_text'] ?? null;
}


function normalizeUrl(string $url): string {
    return rtrim(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH), '/');
}






?>
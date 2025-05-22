<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Converter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php
            // Get api key from https://www.exchangerate-api.com/
            $apiKey = "";

            // Contact API
            $codesUrl = "https://v6.exchangerate-api.com/v6/$apiKey/codes";
            $codesResponse = file_get_contents($codesUrl);
            $currencyOptions = [];

            // Get currency codes
            if ($codesResponse !== false) {
                $codesData = json_decode($codesResponse, true);
                if ($codesData["result"] === "success") {
                    foreach ($codesData["supported_codes"] as $pair) {
                        $currencyOptions[$pair[0]] = $pair[1];
                    }
                } else {
                    echo "<p>Error getting currency codes.</p>";
                }
            } else {
                echo "<p>Failed to connect to currency codes API.</p>";
            }

            // Create currency variables
            $from = htmlspecialchars($_POST["from"] ?? "EUR");
            $to = htmlspecialchars($_POST["to"] ?? "USD");
            $amount = htmlspecialchars($_POST["amount"] ?? "");

            // Convert amount
            if (
                isset($_POST["submit"]) &&
                !empty($_POST["from"]) &&
                !empty($_POST["to"]) &&
                is_numeric($_POST["amount"])
            ) {
                $amount = floatval($amount);
                $url = "https://v6.exchangerate-api.com/v6/$apiKey/latest/$from";
                $response = file_get_contents($url);

                if ($response !== false) {
                    $data = json_decode($response, true);

                    if ($data["result"] ==="success") {
                        $rate = $data["conversion_rates"][$to] ?? null;
                        if ($rate) {
                            $converted = $rate * $amount;
                            echo "<div class='alert alert-success'>";
                            echo "<strong>Exchange rate from $from to $to:</strong> " . round($rate, 4);
                            echo "<br>";
                            echo "<strong>Converted amount:</strong>" . round($converted, 2);
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Invalid target currency.</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>API error: " . ($data["error-type"] ?? "Unknown error") . "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Failed to get exchange rate.</div>";
                }
            }

            // Function to populate drop downs
            function populateCurrencies($options, $selectedCode) {
                $html = "";
                foreach ($options as $code => $name) {
                    $selected = ($code === $selectedCode) ? "selected" : "";
                    $html .= "<option value=\"$code\" $selected>$code - $name</option>";
                }
                return $html;
            }
        ?>
        <!-- Create form -->
        <h1>Currency Converter</h1>
        <br>
        <form method="POST" action="index.php">
            <div class="form-group">
                <label>From</label>
                <select name="from" id="from" class="form-control" required>
                    <?= populateCurrencies($currencyOptions, $from) ?>
                </select>
            </div>
            <div class="form-group">
                <label>To</label>
                <select name="to" id="to" class="form-control" required>
                    <?= populateCurrencies($currencyOptions, $to) ?>
                </select>
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="text" name="amount" id="amount" class="form-control" value="<?= htmlspecialchars($amount) ?>" required>
            </div>
            <br>
            <input type="submit" name="submit" class="btn btn-info" value="Convert">
        </form>
    </div>
</body>
</html>
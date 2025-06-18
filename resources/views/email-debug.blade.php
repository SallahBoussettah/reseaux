<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Delivery Debugger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .result-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        pre {
            background-color: #eee;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Email Delivery Debug Tool</h1>
        <p class="lead">Use this tool to test email delivery and diagnose issues.</p>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Test Email Delivery</h5>
            </div>
            <div class="card-body">
                <form id="emailForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Email Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="verification">Verification Email</option>
                            <option value="feedback">Feedback Email</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="language" class="form-label">Language</label>
                        <select class="form-select" id="language" name="language">
                            <option value="en">English</option>
                            <option value="fr">French</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Test Email</button>
                </form>
            </div>
        </div>
        
        <div class="result-container d-none" id="resultContainer">
            <h4>Results:</h4>
            <div id="resultContent"></div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>Mail Configuration</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Driver:</dt>
                    <dd class="col-sm-9">{{ config('mail.default') }}</dd>
                    
                    <dt class="col-sm-3">Host:</dt>
                    <dd class="col-sm-9">{{ config('mail.mailers.smtp.host') }}</dd>
                    
                    <dt class="col-sm-3">Port:</dt>
                    <dd class="col-sm-9">{{ config('mail.mailers.smtp.port') }}</dd>
                    
                    <dt class="col-sm-3">Encryption:</dt>
                    <dd class="col-sm-9">{{ config('mail.mailers.smtp.encryption') }}</dd>
                    
                    <dt class="col-sm-3">From Address:</dt>
                    <dd class="col-sm-9">{{ config('mail.from.address') }}</dd>
                    
                    <dt class="col-sm-3">From Name:</dt>
                    <dd class="col-sm-9">{{ config('mail.from.name') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('emailForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const type = document.getElementById('type').value;
            const language = document.getElementById('language').value;
            
            // Clear previous results
            document.getElementById('resultContent').innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>';
            document.getElementById('resultContainer').classList.remove('d-none');
            
            // Send the request
            fetch('/test-email-delivery', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email, type, language })
            })
            .then(response => response.json())
            .then(data => {
                let resultHtml = '';
                
                if (data.success) {
                    resultHtml = '<div class="alert alert-success">Email sent successfully!</div>';
                } else {
                    resultHtml = `<div class="alert alert-danger">Error: ${data.message}</div>`;
                }
                
                resultHtml += '<h5 class="mt-3">Details:</h5>';
                resultHtml += `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                
                document.getElementById('resultContent').innerHTML = resultHtml;
            })
            .catch(error => {
                document.getElementById('resultContent').innerHTML = `
                    <div class="alert alert-danger">
                        Network error: ${error.message}
                    </div>
                `;
            });
        });
    </script>
</body>
</html> 
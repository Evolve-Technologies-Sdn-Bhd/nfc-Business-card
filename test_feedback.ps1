# Test script to submit feedback via curl
# This will help us see the exact error from the backend

$url = "http://localhost:8000/api/chatbot/feedback"
$body = @{
    category = "bug"
    message = "Test feedback message"
    user_name = "Test User"
    user_email = "test@example.com"
    rating = 5
} | ConvertTo-Json

Write-Host "URL: $url"
Write-Host "Body: $body"
Write-Host "===== Sending request ====="

try {
    $response = Invoke-WebRequest -Uri $url `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "Accept" = "application/json"
        } `
        -Body $body
    
    Write-Host "Status Code: $($response.StatusCode)"
    Write-Host "Response: $($response.Content)"
} catch {
    Write-Host "Error Status Code: $($_.Exception.Response.StatusCode.Value)"
    Write-Host "Error Message: $($_.Exception.Message)"
    
    # Try to read the response body
    try {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        $reader.Close()
        Write-Host "Response Body:"
        Write-Host $responseBody
        
        # Parse JSON if possible
        $json = $responseBody | ConvertFrom-Json
        Write-Host "Parsed JSON:"
        $json | ConvertTo-Json -Depth 10
    } catch {
        Write-Host "Could not parse response"
    }
}

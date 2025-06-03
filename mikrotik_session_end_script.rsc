# MikroTik Session End Script for Data Usage Tracking
# This script should be added to your MikroTik router to track data usage when users log off
# Place this in your Hotspot User Profile's "On Logout" script

# Get session variables
:local mac $"mac-address"
:local bytesIn $"bytes-in"
:local bytesOut $"bytes-out"
:local user $"user"

# Log the user logout with data usage
:log info "User $user with MAC $mac logged off. Bytes in: $bytesIn, Bytes out: $bytesOut"

# Send data to the Laravel API
/tool fetch url="https://YOUR_SERVER_URL/api/update-data-usage" \
    http-method=post \
    http-header-field="Content-Type: application/json" \
    http-data="{\"mac_address\": \"$mac\", \"bytes_in\": $bytesIn, \"bytes_out\": $bytesOut}" \
    keep-result=no

# ---- INSTALLATION INSTRUCTIONS ----
# 1. Replace YOUR_SERVER_URL with your actual server URL
# 2. To apply this script to all user profiles, run these commands:

# For free users
/ip hotspot user profile set [find name="free_user"] on-logout=":local mac \$\"mac-address\"; :local bytesIn \$\"bytes-in\"; :local bytesOut \$\"bytes-out\"; :local user \$\"user\"; :log info \"User \$user with MAC \$mac logged off. Bytes in: \$bytesIn, Bytes out: \$bytesOut\"; /tool fetch url=\"https://YOUR_SERVER_URL/api/update-data-usage\" http-method=post http-header-field=\"Content-Type: application/json\" http-data=\"{\\\"mac_address\\\": \\\"\$mac\\\", \\\"bytes_in\\\": \$bytesIn, \\\"bytes_out\\\": \$bytesOut}\" keep-result=no"

# For premium users
/ip hotspot user profile set [find name="premium_user"] on-logout=":local mac \$\"mac-address\"; :local bytesIn \$\"bytes-in\"; :local bytesOut \$\"bytes-out\"; :local user \$\"user\"; :log info \"User \$user with MAC \$mac logged off. Bytes in: \$bytesIn, Bytes out: \$bytesOut\"; /tool fetch url=\"https://YOUR_SERVER_URL/api/update-data-usage\" http-method=post http-header-field=\"Content-Type: application/json\" http-data=\"{\\\"mac_address\\\": \\\"\$mac\\\", \\\"bytes_in\\\": \$bytesIn, \\\"bytes_out\\\": \$bytesOut}\" keep-result=no"

# For banned users
/ip hotspot user profile set [find name="banned_user"] on-logout=":local mac \$\"mac-address\"; :local bytesIn \$\"bytes-in\"; :local bytesOut \$\"bytes-out\"; :local user \$\"user\"; :log info \"User \$user with MAC \$mac logged off. Bytes in: \$bytesIn, Bytes out: \$bytesOut\"; /tool fetch url=\"https://YOUR_SERVER_URL/api/update-data-usage\" http-method=post http-header-field=\"Content-Type: application/json\" http-data=\"{\\\"mac_address\\\": \\\"\$mac\\\", \\\"bytes_in\\\": \$bytesIn, \\\"bytes_out\\\": \$bytesOut}\" keep-result=no"

:log info "Session end script installed successfully!" 
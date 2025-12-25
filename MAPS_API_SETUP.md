# Google Maps API Setup

To enable the bin location map feature, you need to:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the "Maps JavaScript API"
4. Create credentials (API Key)
5. Replace `YOUR_API_KEY` in `dashboard.php` with your actual API key

## Security Note
For production, restrict your API key to specific domains and APIs to prevent unauthorized usage.

## Alternative
If you don't want to use Google Maps, you can replace the map section with a simple list view of bin locations.


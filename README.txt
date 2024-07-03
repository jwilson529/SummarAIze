=== SummarAIze - Generate Key Takeaways with AI ===
Contributors: [your_username]
Tags: ai, key points, summary, content enhancement
Requires at least: 5.0
Tested up to: 6.1
Stable tag: 1.1.2
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

# SummarAIze - Generate Key Takeaways with AI

## Description

SummarAIze is a WordPress plugin that allows you to automatically generate and display the top 5 key points of your posts. Enhance your content by providing readers with quick takeaways, making your articles more engaging and accessible.

## Features

- Automatically generate top 5 key points from post content.
- Display key points in various styles and positions.
- Customizable through settings for display mode and position.
- User-friendly interface for managing key points.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/summaraize` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->SummarAIze screen to configure the plugin.
4. Use your own Open AI API key.
5. Use the default Assistant ID or configure your own.

## Important Note

The Assistant ID configures the behavior and responses of the SummarAIze assistant. Users must use their own OpenAI API key for authentication and billing.

## Frequently Asked Questions

### How does the plugin generate key points?

The plugin uses and Open AI Assistant that is created specifically for this plugin. It returns 5 key points for any given article in a predictable, repeatable format. This is NOT using Chat-GPT completions. 

### Can I customize the display of the key points?

Yes, you can customize the display mode and position through the plugin settings. You can choose above or below the content, dark and light mode and a popup version with customizable buttons. 

You can override the main settings per post or page by checking the Override Settings box and choosing new values for the view and mode.

## Changelog

### 1.0.0
* Initial release

## Upgrade Notice

### 1.0.0
* Initial release

## License

This plugin is licensed under the GPLv2 or later.

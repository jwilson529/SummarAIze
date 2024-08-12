=== SummarAIze - Generate Key Takeaways with AI ===
Contributors: jwilson529
Donate link: https://oneclickcontent.com/donate/
Tags: ai, key-points, summary, content-summary, content-enhancement
Requires at least: 5.0
Tested up to: 6.6.1
Stable tag: 1.1.6
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

# SummarAIze - Generate Key Takeaways with AI

## Description

SummarAIze leverages AI to instantly distill your posts into 5 essential takeaways, boosting reader engagement and making your content more digestible at a glance.

Whether you're a blogger looking to highlight essential points, a marketer wanting to ensure your audience gets the most out of your content, or a news site aiming to provide quick summaries, SummarAIze enhances user engagement and retention by providing concise, easily digestible summaries.

### Important Information

SummarAIze relies on the OpenAI API to generate key takeaways. This means that data from your site will be sent to OpenAI's servers for processing, and results will be returned to your site. By using this plugin, you agree to OpenAI's [Terms of Use](https://openai.com/terms) and [Privacy Policy](https://openai.com/privacy).

### API Endpoints Used
- **https://api.openai.com/v1/threads/{thread_id}/messages**: Used to manage messages within the AI assistant.
- **https://api.openai.com/v1/threads/{thread_id}/runs**: Used to manage execution runs for generating content.
- **https://api.openai.com/v1/threads/{thread_id}/runs/{run_id}/submit_tool_outputs**: Used to submit and manage tool outputs related to the assistant.
- **https://api.openai.com/v1/models**: Used to retrieve available models for the assistant and verify the API key. 

### Features

- **AI-Powered Content Summarization**: Automatically generate top 5 key points from post content using AI.
- **Customizable Display Options**: Display key points in various styles and positions to suit your theme.
- **User-Friendly Interface**: Easily manage and customize key points for each post or page.
- **Flexible API Integration**: Integrate with your own OpenAI API key, allowing for control over usage and billing.
- **Assistant Configuration**: Use the default Assistant ID or configure your own in the OpenAI Playground.
- **Enhance SEO and Readability**: Improve your content's SEO by providing search engines with structured summaries, and enhance readability for your audience.

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- An active OpenAI API key (sign up at [OpenAI](https://openai.com/))
- Awareness of potential costs associated with using the OpenAI API

## Installation

1. Upload the plugin files to the `/wp-content/plugins/summaraize` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Settings->SummarAIze to configure the plugin.
4. Enter your OpenAI API key. (If you don't have one, sign up at [OpenAI](https://openai.com/))
5. Optionally, configure your Assistant ID in the OpenAI Playground.

## Important Note

The Assistant ID configures the behavior and responses of the SummarAIze assistant. Users must use their own OpenAI API key for authentication and billing.

## Getting Started

After installation and activation:
1. Go to the SummarAIze settings page and enter your OpenAI API key.
2. Choose your preferred display options (position, style, etc.).
3. Create or edit a post/page.
4. Use the SummarAIze button in the editor to generate key points.
5. Publish or update your content to see the summarized points on your site.

## Privacy

SummarAIze takes your privacy seriously. The plugin only sends the necessary content to OpenAI's servers for generating key points. No personal data or sensitive information is transmitted. However, please be aware that the content you choose to summarize will be processed by OpenAI. We recommend reviewing OpenAI's privacy policy for more details on their data handling practices.

## Frequently Asked Questions

### How does SummarAIze generate key points?

SummarAIze uses advanced AI algorithms to analyze your post content and extract the top 5 key points. This process is managed through the OpenAI API, ensuring high-quality and relevant summaries.

### Can I customize the display of the key points?

Yes! SummarAIze offers several customization options. You can choose to display key points above or below your content, switch between dark and light modes, and even use a popup version with customizable buttons. Additionally, you can override the main settings per post or page by using the provided shortcode.

### What happens if I don't provide an API key?

The plugin requires an OpenAI API key to function. Without it, the AI-driven features will not be available. Please ensure you have an active OpenAI account and understand the associated costs before using the plugin.

### How secure is the data transmitted to OpenAI?

Data security is a priority. The plugin only transmits the necessary content to OpenAI's servers to generate key points. No other information is shared. Please review OpenAI's [Privacy Policy](https://openai.com/privacy) for more details on how they handle data.

### Are there any costs associated with using SummarAIze?

While the SummarAIze plugin itself is free, it relies on the OpenAI API, which is a paid service. The cost will depend on your usage and OpenAI's current pricing model. We recommend reviewing OpenAI's pricing details and monitoring your API usage to manage costs effectively.

### How can I get support if I run into issues?

If you encounter any issues or have questions about using SummarAIze, you can get support through the [WordPress support forums](https://wordpress.org/support/plugin/summaraize) or by visiting the [official website](https://github.com/jwilson529/SummarAIze).

## Screenshots

1. ![Above or Below Content](assets/above-or-below-content.png)
   *Configure whether the key points appear above or below the content.*

2. ![Dark Mode](assets/dark-mode.png)
   *Display key points in dark mode for a better visual experience.*

3. ![Classic Editor](assets/classic-editor.png)
   *Interface for generating and editing key points in the Classic Editor.*

4. ![Popup View](assets/popup-view.png)
   *Display key points in a popup view.*

5. ![Settings Screen](assets/settings-screen.png)
   *The settings page for configuring display options.*

## Changelog

### 1.1.7
* Initial release in the WordPress repo.

### 1.1.4
* Updated readme file to include the details of the 3rd party services being used.

### 1.1.0
* Changed from using a static Assistant to generating the Assistant via the API.

### 1.0.0
* Initial release

## Upgrade Notice

### 1.1.7
* Initial release in the WordPress repo. Includes all features and improvements from previous versions.

## Future Plans

We're constantly working to improve SummarAIze. Some features we're considering for future updates include:
- Integration with more AI providers


Stay tuned for these exciting updates!

## License

This plugin is licensed under the GPLv2 or later.
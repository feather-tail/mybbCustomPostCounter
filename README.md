# Custom Post Counter Plugin for MyBB

## Overview

The **Custom Post Counter** plugin for MyBB 1.8 allows you to count user posts in specific forums and automatically update a custom field in the user profile. This can be useful for tracking activity in particular forums and displaying the results directly in the user's postbit.

## Features

- Tracks user posts in specified forums.
- Updates a custom field in the user profile with the post count.
- Adds a "Custom Posts" count to the postbit template.
- Automatically adjusts the post count when posts or threads are deleted, soft-deleted, or restored.

## Installation

1. Download the `custompostcounter.php` file and upload it to your MyBB plugin directory (`inc/plugins/`).

2. Log in to your MyBB Admin Control Panel.

3. Navigate to `Plugins` and find **Custom Post Counter** in the list.

4. Click "Install & Activate".

5. Configure the plugin settings by navigating to `Configuration > Settings > Custom Post Counter Settings`.

## Configuration

After installing and activating the plugin, you can configure it by:

1. Going to the `Custom Post Counter Settings` section in the settings panel.
2. Enter the IDs of the forums you want to track, separated by commas (e.g., `2,5,10`).

## Uninstallation

1. Deactivate the plugin in the MyBB Admin Control Panel under `Plugins`.

2. (Optional) To completely remove the plugin and its data, click "Uninstall" after deactivation. This will remove the custom field from the users table and delete the plugin settings.

## Customization

- The post count is displayed in the postbit template under the label **Custom Posts**. You can modify the template or the label to suit your forum's needs by editing the plugin code or modifying the `postbit_author_user` template directly in the MyBB Admin Control Panel.

## Compatibility

- MyBB 1.8.x

## Changelog

### Version 1.0
- Initial release of the Custom Post Counter plugin.


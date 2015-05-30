=== Teamspeak 3 Widget for Wordpress ===
Contributors: Michael Plas ( WP DEV ) ScP from the teamspeak forum ( Framework )
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9328389
Tags: badge, widget, widgets, plugin, plugins, sidebar, teamspeak, teamspeak 3, ts3, ts
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 1.0.3

Allows to show the Users and Channels of a Teamspeak3 as a Widget ( TS VIEWER )

== Description ==

NEW in 1.0.3: I got a lot of Mails with Setup-Problems: The New Version has a more usabile Optionspanel.
If you had already installed the Widget: You CAN remove the Queryport and the :, but the new Version also Supports the old Syntax

Allows to show the Users and Channels of a Teamspeak3 as a Widget ( TS VIEWER )

= Credits =

Uses the Teamspeak 3 Framework by ScP from the teamspeak forum

== Installation ==

1. Unzip `teamspeak3-viewer-widget` and upload the contained files to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


= Add the sidebar widget =

1. Install the `Teamspeak 3 Widget` widget through the 'Design -> Widgets' menu in WordPress

= (Optional, for advanced users) Add `ts3_wp_viewer` to a post or a page =

1. Type `[ts3_wp_viewer]` into a post's or a page's body text.

= (Optional, for advanced users) Add `ts3_wp_viewer` to a template =

1. Enter `<?php echo do_shortcode('[ts3_wp_viewer]'); ?>` into a suitable template file.

== Frequently Asked Questions ==
1. The server shows an error message - what should i do? Try to understand the errorcode, if there still problems mail development@michaelplas.de
2. The Standardports are Queryport: 10011 and VirtualServerport 9989



= Which prerequisites does `ts3_wp_viewer` expect? =

Actually, just SocketFunctions

= How would I modify ts3_wp_viewer's options when it is used as a short tag or inside a template? =

To modify this plugin's option, you will have to add a widget at least once. Once the options are set, they are used for all instances of this plugin and you can safely remove the widget.

== Developer Notes ==

This is just an Beta, so dont cry if there are Problems! if you need help just write a mail to development@michaelplas.de
Special Thank you ScP from the teamspeak.com forum who created the framework

This is just the Widget, a more functional Viewer will follow

== Changelog == 
= 1.0.0 =
* First inoffcial Release
 
= 1.0.1 =
* Upgraded Framework, Release Version

= 1.0.2 =
* usability-update

= 1.0.3 =
* hate debugging, fixed code
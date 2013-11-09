=== Plugin Name ===
Contributors: conormccauley
Donate link: http://www.conormccauley.com/wp-athletics
Tags: athletics, results, running club, athlete, statistics, races, racing, events, athletic, jogging, club, charts, records, personal bests, pb, pr, personal records
Requires at least: 3.0.1
Tested up to: 3.7.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allow your registered users to log and analyse their athletic results. 
It also includes features for generating overall club records and some admin tools for to enter results.

== Description ==

WP Athletics is a powerful tool for your running club aiming to allow your runners to log and track their results, track their PB's and view statistics and charts.

The plugin, designed with simplicity in mind is ready to use out of the box. There are a variety of UI themes included for your convenience.

Some of the major features for your registered users are:
*	Enter race results and create new events if they do not yet exist
*	View their race history and personal bests
*	Search for other athletes and events using the power search tool
*	View individual or club-wide statistics including visual charts
*	View overall club records
*	View recent results

On top of this, there are a selection of administrator tools making your job easier to manage and control the data
*	Easily manage the event categories, age categories, results and events
*	Event merger (say a user has created an event that already exists while logging a new result, you can easily merge the two events using this tool)
*	Manually enter a list of results for a race (and easily add new athletes to the system if they are not registered)
*	Embed an interactive table of results into a new post using a simple shortcode, e.g. [wpa-event id=505]
*	Generate a customised printable rankings list for your dressing room (e.g top 5k female runners in 2012)
*	View a log of plugin activity
*	All the pages (manage results, recent result and records) are automatically generated and ready to use out of the box
*	A recent results widget to display the last 5 (customisable) results based on event date

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the folder 'wp-athletics' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Ensure the generated WP Athletics pages are configured to use a full-page template
1. Link to the generated pages however you wish

== Frequently Asked Questions ==

= Ok, it's installed, how do I get started? =

Good question! There is essentially zero-configuration to get started with this plugin, you simply need to link to the generated pages once activated and you're set.

The main component of this plugin is the "Manage Results" page which is the entry point for users logging and analysing their result.
This page will automatically be created for you when the plugin is activated and should be left as it is except for changing the template to a full-page 
width of your current theme if that is supported. You simply need to create a link to the page for your (registed) users, and that's it! How you do that is completely up to you. 

Other pages that will probably be of interest to you are the club records page and the recent results page which again are automatically created and you can link 
to them however you wish. These are optional of course but the club records will probably be of interest to your users.

Finally there is a recent results sidebar widget which will display the last 5 (or whatever you wish) results based on their event date.

= How do athletes register? =

Users simply register using the wordpress register tool, meaning existing users can start using the plugin no problem. 
When they visit the "Manage Results" page for the first time, they'll be asked to enter their DOB and gender and then they're ready to go.

= Can I print the club rankings? =

Yes! Use the "Print Rankings" feature on the admin dashboard, select your filters and click "print" and that's it. This feature is highly customisable, for example 
you could print the 5k track rankings for males in 2010 (if that's any use to you)

= Can I control the data if a user makes a mistake =

Yes! There is a powerful result management admin tool allowing you to edit/delete any results, or even assign results to another use if a mistake was made. 

= Can I choose my own event categories (i.e 100m, 5k, 10k etc)

Yes, there is an admin tool for adding, editing and removing the event categories, you can also specify which categories should appear in on the records page.

= Can I choose my own age categories (i.e Junior, Senior, 30-35 etc)

Yes, there is an admin tool for adding, editing and removing the age categories. Each age category shall appear as a separate tab on the records page.

= Can I change the look of the plugin? =

There are 4 themes available, the default is a gray theme but there is also a blue, yellow and red theme available. If you would like another one just get in touch. 

Is there language support?

There is language support, but currently it's only available in English. I hope to add new languages as the plugin progressed but this is low priority for the moment.
If you want to help translate to a particular language, please get in touch. 

Can I have separate male/female records pages?

Yes! By default both male and female results are displayed on the records page with a gender filter above. If you wish to display the male and female records on separate 
pages, on the WP-Athletics settings page, choose the "separate" option for records mode. You will now see that two new pages have been generated and the old records 
page has been delete. You need to ensure the new generated pages are using a full-width page template. 

== Screenshots ==

1. The "Manage Results" screen from which users can view their race history, personal records, enter new results and view statistics.
2. The interactive personal bests tab, users can click on the ranking to view athletes around them.
3. The statistics feature, users can switch between individual or club-wide statistics. 
4. Users can view individual event statistics and visually see how they have been performing.
5. Click on an event and see who else ran and how they did.
6. The interactive club records page complete with powerful filters.
7. On the records page, click on the chart button for a particular event (e.g 5k) and see the full rankings.
8. The automatically generated recent results page shows a facebook-like news feed of recent athlete activity.
9. The recent results widget is a useful sidebar widget showing recent athlete activity (number of results displayed is customisable).
10. Athletes can add new results in a few clicks.
11. There are many admin tools available to customise the age categories, event categories, manage results and events and print rankings.
12. The power search tool available on all pages, searched both athletes and event results and when clicked opens in a friendly pop-up dialog. 

== Changelog ==

= 1.0.0 =
* Initial launch of WP Athletics
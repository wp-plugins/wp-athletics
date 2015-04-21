=== Plugin Name ===
Contributors: conormccauley
Donate link: http://www.conormccauley.me/wordpress-athletics
Tags: athletics, results, sports, track, running club, athlete, statistics, races, racing, events, athletic, jogging, club, charts, records, personal bests, pb, pr, personal records
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Record, compare and analyse your athletic results. Plan events, track personal bests, compare age graded leaderboards and more.

== Description ==

<b>New in version 1.1.0 - Events! View past and plan future events</b>
<i>The event page should automatically be created once you update the plugin</i>

Wordpress Athletics is a powerful collection of features for your athletics club website aiming to allow your athletes to log and analyze their results, track their personal bests and view statistics and charts.
Users can view and plan future events and there are 2 useful widgets to display recent results and upcoming events. The plugin, designed with simplicity in mind is ready to use out of the box. There are also a variety of UI themes included for your convenience.

See a working demo of the plugin on my former running clubs website: http://www.donoreharriers.com

Some of the major features for your registered users are:
<ul>
<li>Enter race results and create new events</li>
<li>View their race history and personal bests</li>
<li>Search for other athletes and events using the power search tool</li>
<li>View individual or club-wide statistics including visual charts</li>
<li>View overall club records</li>
<li>View recent results</li>
<li>Event Manager, plan future races and see who else is going</li>
</ul>

On top of this, there are a selection of administrator tools making your job easier to manage and control the data:
<ul>
<li>Create and edit athletes and their results</li>
<li>Easily manage the event categories, age categories, results and events</li>
<li>Manually enter a list of results for a race (and easily add new athletes to the system if they are not registered)</li>
<li>Embed an interactive table of results into a new post using a simple shortcode, e.g. [wpa-event id=505]</li>
<li>Generate a customised printable rankings list for your dressing room (e.g top 5k female runners in 2012)</li>
<li>View a log of plugin activity</li>
<li>All the pages (manage results, events, recent result and records) are automatically generated and ready to use out of the box</li>
<li>A recent results widget to display the last 5 (customisable) results based on event date</li>
<li>An upcoming events widget to display the next 5 events in the calendar</li>
<li>Event merger (say a user has created an event that already exists while logging a new result, you can easily merge the two events using this tool)</li>
</ul>

Note: Athlete registration and participation is not necessary for this plugin to work effectively (i.e bypassing the "Manage Results" screen).
It is perfectly ok for an administrator to handle manual entry of all athletic results without users having to register or enter results, the administrator 
can also create new athlete profiles when necessary. 

== Installation ==

1. Upload the folder 'wp-athletics' to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Ensure the generated WP Athletics pages are configured to use a full-page template
1. Review the age categories and event categories and edit as you wish
1. Link to the generated pages however you wish and you're read to go!

== Frequently Asked Questions ==

= Ok, it's installed, how do I get started? =

Good question! There is essentially zero-configuration to get started with this plugin, you simply need to link to the generated pages once activated and you're set (though you should first modify the generated pages to use a full-width page template)

The main component of this plugin is the "Manage Results" page which is the entry point for users logging and analysing their result.
This page will automatically be created for you when the plugin is activated and should be left as it is except for changing the template to a full-page 
width of your current theme if that is supported. You simply need to create a link to the page for your (registed) users, and that's it! How you do that is completely up to you. 

Other pages that will probably be of interest to you are the club records page and the recent results page which again are automatically created and you can link 
to them however you wish. These are optional of course but the club records will probably be of interest to your users.

Finally there is a recent results sidebar widget which will display the last 5 (or whatever you wish) results based on their event date.

= How do athletes register? =

Users simply register using the wordpress register tool, meaning existing users can start using the plugin no problem. 
When they visit the "Manage Results" page for the first time, they'll be asked to enter their DOB and gender and then they're ready to go.

= Do my athletes HAVE to register? =

No. You can use this plugin by completely bypassing user registration and the "Manage Results" page and just utilizing the records page. The administrator has the ability to create athlete profiles manually 
and enter results for them meaning no athlete registration is necessary. However, to get the best out of this plugin, it is advised to allow users contribute their own
data as they can include historic results meaning more accurate records and more user interaction. The idea behind this plugin was to foster healthy inter-club competition and it will work better when users can manage their own results.

= Can I print the club rankings? =

Yes! Use the "Print Rankings" feature on the admin dashboard, select your filters and click "print" and that's it. This feature is highly customisable, for example 
you could print the 5k track rankings for males in 2010 (if that's any use to you)

= What if I (as an admin) want to enter results for an athlete that is not registered? =
In the admin tools, in the "Add Results" section, there is a tool for creating a profile for athletes that are not yet registered on the site. A username and password 
will be generated and you can provide these details to the athlete at your discretion. 

= Can I embed a table of race results into a news post? =
Sure! Simply use the shortcode [wpa-event id=xxx] (where xxx is the event ID). To find the event ID, simply navigate to the "Manage Events" screen in the admin area.

= Can I control the data if a user makes a mistake? =

Yes! There is a powerful result management admin tool allowing you to edit/delete any results, or even assign results to another use if a mistake was made. 

= Can I choose my own event categories? (i.e 100m, 5k, 10k etc) =

Yes, there is an admin tool for adding, editing and removing the event categories, you can also specify which categories should appear in on the records page.

= Can I choose my own age categories? (i.e Junior, Senior, 30-35 etc) =

Yes, there is an admin tool for adding, editing and removing the age categories. Each age category shall appear as a separate tab on the records page.

= Can I change the style of the plugin? =

There are 4 themes available for your convenience. The default is a gray theme but there is also a blue, yellow and red theme available. If you would like another one just get in touch. 

= Is there language support? =

Yes there is language support, the plugin is currently availale in:
1. English
1. Italian
1. Dutch
1. German
1. Portuguese

If you feel you can help translate the plugin into a language not yet supported, please get in touch.

= Can I have separate male/female records pages? =

Yes! By default both male and female results are displayed on the records page with a gender filter above. If you wish to display the male and female records on separate 
pages, on the WP-Athletics settings page, choose the "separate" option for records mode. You will now see that two new pages have been generated and the old records 
page has been delete. You need to ensure the new generated pages are using a full-width page template. 

== Screenshots ==

1. The "Manage Results" screen from which users can view their race history, personal records, enter new results and view statistics.
2. The interactive personal bests tab, users can click on the ranking to view athletes around them.
3. The statistics feature, users can switch between individual or club-wide statistics. 
4. Athletes can add new results in a few clicks.
5. Click on an event and see who else ran and how they did.
6. The interactive club records page complete with powerful filters.
7. The events page allows to you view past and future event results, plan a race, see who else is going and also allows athletes submit events.
8. The automatically generated recent results page shows a facebook-like news feed of recent athlete activity.
9. The recent results and upcoming events widgets.

== Upgrade Notice ==

= 1.0.0 =
First version of WP Athletics.

= 1.0.1 =
Minor bug fixes

= 1.0.2 =
If you're having an issue with profile photos not displaying, why not upgrade! it's fixed :)

= 1.0.3 =
Minor enhancements, Italian language now supported and a fix for users having trouble creating SQL views.

= 1.0.4 =
Layout changes and minor bug fixes 

== Changelog ==

= 1.0.0 =
First version of WP Athletics.

= 1.0.1 =
Minor bug fixes, release documentation

= 1.0.2 =
Bug fix with profile photos not displaying correctly

= 1.0.3 =
Minor enhancements, Italian now supported and a fix for users having trouble creating SQL views.

= 1.0.4 =
Layout changes and minor bug fixes. 
Height of admin inputs, profile photo fix, embedded results fix, transparent chart backgrounds, admin area not translated.

= 1.0.5 =
German translation added

= 1.0.6 =
Dutch translation added. Manage athletes tool added. Fix for invalid event date. Some other bug fixes and UI tweaks.

= 1.1.0 =
A good amount of change in this release, hope it doesn't break anything :) Major addition is the events page and supporting functions. 
- Events page added (should be auto created if updating). Users can view past and upcoming events, plan races and see who else is running. Users can also submit events.
- Admin can add upcoming events via the dashboard (Manage Events)
- UI updates and bug fixes
- Upcoming events widget added
- Activity link support all major players - Garmin,Strava,RunKeeper etc.

= 1.1.1 = 
Minor bug fixes. Force update of database on activate.

= 1.1.2 =
More bug fixing and minor tweaks. Now displays upcoming events on a users profile.

= 1.1.3 =
I'm a busy man today. Disable WPA on non-WPA pages by default, causing a lot of problems. Ability to switch back on in settings.

= 1.1.4 =
You can now add extra detail to your events - description, contact info, website urls, cost etc.    
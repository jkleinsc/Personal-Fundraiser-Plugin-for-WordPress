=== Personal Fundraiser ===
Contributors: johnkleinschmidt
Donate link: http://cure.org/donate
Tags: fundraising, paypal
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: trunk

Expand your fundraising base by getting your donors and fans involved in the fundraising process.

== Description ==

Expand your fundraising base by getting your donors and fans involved in the fundraising process.

This plugin provides organizations the ability to allow their fans and constituents to create their own custom online fundraisers using PayPal donations and other payment methods.

All donations for this plugin go to provide life-changing surgery for children in the developing world. Learn more at <a href="http://cure.org/curekids">CURE International</a>.

== Installation ==

1. Upload the plugin to your plugins directory and activate.
1. Check the settings by clicking on the Personal Fundraiser admin menu item.
1. Define the Personal Fundraiser Fields that users will fill out when creating a personal fundraiser campaign.
1. Create a new Cause from the Causes admin menu using the shortcode **[pfund-edit]** as well as the shortcode from the Personal Fundraiser settings.
1. Navigate to /`<CAUSE SLUG>` to see the list of available causes to create campaigns from.

== Frequently Asked Questions ==

= Why should I use this plugin? =

charity: water has raised over $9.3M using personal fundraising campaigns.  
This plugin adds the capability of creating personal fundraising campaigns on your WordPress site. 

= What is a Cause? =

A Cause is a template used to create fundraising campaigns. 
As an administrator you define the look and feel of the campaigns that your users create.   

When creating or editing a cause, here are several things to keep in mind:

1. Every cause needs to have the shortcode **[pfund-edit]** in the content.  This enables the ability for users to create and/or edit campaigns.
1. Every cause should also have the shortcode **[pfund-donate]** in the content.  This enables the ability for donations to be collected on campaigns created from the cause.
1. Besides the content specified in the WYSIWYG editor, each cause has a description and image field.  These fields are displayed on the cause list page.
1. If you update the look and feel of a cause, all of the campaigns created from that cause will also have an updated look and feel.

= What shortcodes does this plugin provide? =

Each of the fields defined in the Personal Fundraiser Fields section of the Personal Fundraiser settings has a corresponding shortcode.  In addition, the plugin provides the following shortcodes:

* **[pfund-campaign-list]** Display the list of campaigns
* **[pfund-cause-list]** Display the list of causes
* **[pfund-comments]** Display the comments/donations for the campaign.
* **[pfund-donate]** PayPal donate button.  This shortcode is required in order to accept donations for the campaigns.
* **[pfund-edit]** Required shortcode to display edit button
* **[pfund-camp-title]** The title of the campaign
* **[pfund-gift-goal]** The amount that the user hopes to raise for their campaign
* **[pfund-gift-tally]** The total amount raised

= How do I use PayPal? =

Using PayPal requires two settings on the Personal Fundraising settings screen.  These values can be obtained by logging into PayPal:

**Donate Button Code**

1. Click on Merchant Services.
1. Under Create Buttons, click on Donate.
1. Fill in your Organization name and optionally fill in a Donation ID.  Both these values will be displayed to the user during the PayPal checkout process.
1. Optionally, you may customize the text or appearance of the button.
1. For contribution amount, select *Donors enter their own contribution amount*.
1. For Merchant account IDs, select *Use my secure merchant account ID*.
1. Click on Create Button.
1. Copy all of the HTML code from the Website tab and paste it in the *Donate Button Code* field.

**Payment Data Transfer Token**

1. Click My Account tab.
1. Click on Profile.
1. Click Website Payment Preferences in the Seller Preferences column. 
1. Make sure that Auto Return for Website Payments is turned on.
1. For Return URL, specify your site's URL. The plugin will override this value to return to the campaign once a donation is processed.
1. Under the Payment Data Transfer (optional) section, make sure that Payment Data Transfer is On.  If it is already on, copy the Identity Token value and paste it into the *Payment Data Transfer Token* field.  If it is not on, turn it on, click on Save at the bottom of the page and then click Website Payment Preferences in the Seller Preferences column to display the screen again with the  Identity Token value.


== Screenshots ==

== Changelog ==

= 0.7 =

* Initial public release

== To Do ==

* Reporting on all campaigns
* Finish progress bar shortcode
* Tweak UI


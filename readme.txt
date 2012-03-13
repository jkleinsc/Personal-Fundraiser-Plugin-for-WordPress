=== Personal Fundraiser ===
Contributors: johnkleinschmidt
Donate link: http://cure.org/donate
Tags: fundraising, paypal
Requires at least: 3.1
Tested up to: 3.3.1
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
1. Edit/View the sample cause **Help Raise Money For My Cause** to see a sample cause.
1. Create a new Cause from the Causes admin menu using the shortcode **[pfund-edit]** as well as the shortcode from the Personal Fundraiser settings.
1. Navigate to /`<CAUSE SLUG>` to see the list of available causes to create campaigns from.

== Frequently Asked Questions ==

= Why should I use this plugin? =

charity: water has raised over $10M using personal fundraising campaigns.
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

* **[pfund-campaign-list]** Display the list of campaigns.
* **[pfund-campaign-permalink]** Display the permalink(URL) for the campaign.
* **[pfund-cause-list]** Display the list of causes.
* **[pfund-comments]** Display the comments/donations for the campaign.
* **[pfund-donate]** PayPal donate button.  This shortcode is required in order to accept donations for the campaigns.
* **[pfund-edit]** Required shortcode to display edit button.
* **[pfund-camp-title]** The title of the campaign.
* **[pfund-days-left]** The number of days left in the campaign (if an end date is specified for the campaign).
* **[pfund-gift-goal]** The amount that the user hopes to raise for their campaign.
* **[pfund-gift-tally]** The total amount raised.
* **[pfund-giver-list]**  Displays a list of supporters for the current campaign.  The supported attributes are:
     * **max_givers**  Maximum number of supporters to display.  If this attribute is specified and the number of supporters exceeds this value, a set of randomized supporters will be returned.
     * **row_max**  Number of supporters to display in one row.  This attribute is simply used to add a css class to the last supporter in a row.  The class is specified by the row_end_class attribute.
     * **row_end_class**  Class to apply to last support in a row.  This class is applied to the last supporter in a row.
* **[pfund-giver-tally]** The total number of unique donors to the campaign.
* **[pfund-progress-bar]** Displays a progress bar for the campaign.  This progress bar displays how much of the campaign goal has been achieved.
* **[pfund-user-avatar]** The avatar of the user who created the campaign.  You can specify the size of the avatar by passing a "size" attribute (e.g. [pfund-user-avatar size="50"].

= What do the various settings do? =
* **Campaign Slug** URL prefix for campaigns.  Also the location of the page containing the list of causes.
* **Cause Slug** URL prefix for causes.  Also the location of the page containing the list of campaigns.
* **Currency Symbol** Currency symbol to display next to monetary amounts such as amount of donations received.
* **Date Format** Date format to use to display dates.  The date formats correspond to the ones defined at: <a href="http://codex.wordpress.org/Formatting_Date_and_Time">http://codex.wordpress.org/Formatting_Date_and_Time</a>.  This plugin only supports the following subset of formatting characters:
     * **d** Day of the month.
     * **j** Day of the month with no leading zeros
     * **m** Numeric month leading zeros
     * **n** Numeric month without leading zeros
     * **Y** Full numeric year, 4 digits
     * **y** Numeric year: 2 digits
* **Login Required To Create**  If checked, users must be logged in before they can create campaigns.  If it is not checked, users may create campaigns anonymously, but those campaigns stay in draft status until the user logs in.  Once the user logs in the campaign is assigned to that user.
* **Allow Users To Register**  If users are not logged in, allow them to register using the custom registration the personal fundraiser plugin provides.
* **Campaigns Require Approval**  If checked, campaigns for logged in users are saved as Pending Review until an admininstrator can approve them.  Campaigns pending review are only visible to the creator of the campaign as well as admins.  If this checkbox is not checked, campaigns will be publicly visible as soon as a logged in user saves them.
* **User Roles that can submit campaigns** Determines what user roles can create campaigns.  This setting only affects anonymous campaigns after a user logs in.  If a campaign is created anonymously, but the logged in user doesn't have rights to submit a campaign, the campaign will stay in a draft status indefintely.
* **PayPal Options** See below
* **MailChimp Options** This plugin provides the ability to send transactional emails through MailChimp.  The transactional emails must be created using MailChimp's <a href="http://apidocs.mailchimp.com/api/">API</a>.
* **MailChimp API key** MailChimp API key needed to send emails via MailChimp.  This API key can be obtained by going to: <a href="http://admin.mailchimp.com/account/api-key-popup">API Key</a>.
* **Campaign Approval Email ID**  The MailChimp campaign id of the transactional campaign to use to send campaign approved emails.  The following merge fields are passed:
    * **NAME** The display name of the user who created the campaign.
    * **CAMP_TITLE** The title of the campaign.
    * **CAMP_URL** The URL for the campaign.
* **Campaign Donation Email ID** The MailChimp campaign id of the transactional campaign to use to send campaign donation emails.  The following merge fields are passed:
    * **NAME** The display name of the user who created the campaign.
    * **CAMP_TITLE** The title of the campaign.
    * **CAMP_URL** The URL for the campaign.
    * **DONATE_AMT** The amount donated.
    * **DONOR_ANON** If the donation was an anonymous donation, this value will be "true".
    * **DONOR_FNAM** Donor's first name (if not an anonymous donation).
	* **DONOR_LNAM** Donor's last name (if not an anonymous donation).
	* **DONOR_EMAL** Donor's email(if not an anonymous donation).
* **Goal Reached Email ID** The MailChimp campaign id of the transactional campaign to use to send an email when the campaign goal is reached.  The following merge fields are passed:
    * **NAME** The display name of the user who created the campaign.
    * **CAMP_TITLE** The title of the campaign.
    * **CAMP_URL** The URL for the campaign.
    * **GOAL_AMT** The goal that was reached.
* **Personal Fundraiser Fields** Defines the fields that are available for use for personal fundraisers.  Each field has the following settings:
    * **Label** The text to display to identify this field on the campaign creation/edit screen.
    * **Description** A longer text description of the field that will be displayed on the campaign creation/edit screen.
    * **Type** Defines the type of field.  The following types are available:
        * **Date Selector** Displays a date picker to the user.
        * **Campaign Title** The field used to contain the campaign title.  This field is required and the type cannot be changed. 
        * **Campaign URL slug** The field used to contain the campaign url suffix.  This field is required and the type cannot be changed.
        * **End Date**  The field used to contain the campaign end date.  The type of this field cannot be changed.
        * **Large Text Input (textarea)** Provides a large textbox for users to provide text.
        * **Image** Provides an image upload.
        * **Select Dropdown** Displays a dropdown for users to select a value.
        * **Text Input** Simple text input field.
        * **Fixed Input** Intended for future development.  Generally the idea of this field is pass along fixed data when a donation is processed.  Useful if you need additional fields to categorize donations.
        * **User Email** Email field that will default to the user's email but can be overriden with any valid email address.
        * **User Display Name** Text input field that defaults to the user's display name but can be overriden with any value.
        * **User Goal** The field used to contain the campaign goal.  This field is required and the type cannot be changed.
   * **Data** Currently only used by Select Dropdown fields, this field defines the valid values for the drop down.  Each value for the drop down should be separated by a new line.
   * **Required** If this checkbox is checked, this field is required in order to create or update the campaign.
   * **Shortcode**  The shortcode to use to display this field on the campaign.
   * **Actions** Actions that can be taken on the field:
        * Delete the field.  Note that the field will not actually be deleted until you click on the "Save Changes" button.
        * Move Up/Move Down Change the display order of the field on the campaign creation/edit screen.

= How do I use PayPal? =
 
Using PayPal requires a Premier or Business PayPal account.  In order to work properly with the Personal Fundraiser plugin, there are several settings that must be set on your PayPal account.  Also there two fields on the Personal Fundraising settings screen that must be set.  These values can be obtained by logging into PayPal:

**Donate Button Code**

1. Click on Merchant Services.
1. Under Create Buttons, click on Donate.
1. Fill in your Organization name and optionally fill in a Donation ID.  Both these values will be displayed to the user during the PayPal checkout process.
1. Optionally, you may customize the text or appearance of the button.
1. For contribution amount, select *Donors enter their own contribution amount*.
1. For Merchant account IDs, select *Use my secure merchant account ID*.
1. Click on Create Button.
1. Copy all of the HTML code from the Website tab and paste it in the *Donate Button Code* field.

PayPal has more information on using a donate button here: <a href="https://www.paypal.com/us/cgi-bin/?cmd=_donate-intro-outside">PayPal Donate Button</a>.

**Payment Data Transfer Token**

1. Click My Account tab.
1. Click on Profile.
1. Click on My Selling Tools under My Profile on the left hand side of the screen.
1. Under the "Selling Online" section, click on *Update* for the line titled *Website Preferences*.
1. Make sure that Auto Return for Website Payments is turned on.
1. For Return URL, specify your site's URL. The plugin will override this value to return to the campaign once a donation is processed.
1. Under the Payment Data Transfer (optional) section, make sure that Payment Data Transfer is On.  If it is already on, copy the Identity Token value and paste it into the *Payment Data Transfer Token* field.  If it is not on, turn it on, click on Save at the bottom of the page and then click Website Payment Preferences in the Seller Preferences column to display the screen again with the  Identity Token value.

**Instant Payment Notification**

Instant Payment Notification or IPN is a secondary measure to ensure that a PayPal donation is recorded by the personal fundraiser.  This is used in cases where a donor closes their browser window after submitting a donation but before returning to your site.

1. Click My Account tab.
1. Click on Profile.
1. Click on My Selling Tools under My Profile on the left hand side of the screen.
1. Under the "Getting paid and managing my risk" section, click on *Update* for the line titled *Instant payment notifications*.
1. If instant payment notifications is turned off, click on the button that says *Choose IPN Settings*.  If it is turned on, you do not need to do anything else.
1. For Notification URL, specify your site's URL. The plugin will override this value.
1. For IPN messages, select Receive IPN messages (Enabled).
1. Click on Save to save your settings.


**Use PayPal Sandbox**

If you are using PayPal's developer sandbox for testing, check this checkbox; otherwise leave it unchecked.

= How do I use Authorize.Net? =
 
Using Authorize.Net first requires that you have a SSL Certificate for your site and the capability of running at least part of your site via https.

In order to work properly with the Personal Fundraiser plugin, there are several settings that must be obtained from your Authorize.Net account.  These values can be obtained by logging into Authorize.Net:

**API Login ID**

1. Click on Account.
1. Under Security Settings, click on API Login ID and Transaction Key.
1. If an API Login ID value has already been generated and is visible, copy the API Login ID from the API Login ID and Transaction Key screen and paste it in the *API Login ID* field on the Personal Fundraiser settings screen.
1. If an API Login ID value has not already been generated: do the following, type in your Secret Answer. You should have configured a Secret Question and Secret Answer during account activation.
1. Click Submit to continue. The API Login ID and Transaction Key generated for your payment gateway account appear.  Copy these values to the corresponding values on the Personal Fundraiser settings screen.

**Transaction Key**

1. Click on Account.
1. Under Security Settings, click on API Login ID and Transaction Key.
1. Under the section titled Create New Transaction Key, type in your Secret Answer. You should have configured a Secret Question and Secret Answer during account activation.
1. Click Submit to continue. The API Login ID and Transaction Key generated for your payment gateway account appear.  Copy these values to the corresponding values on the Personal Fundraiser settings screen.

**Product/Donation name** 

Specify the description that should be used for the transactions.  If this is empty, this will default to "Donation".

**Use SSL**

Unless you are using the Authorize.Net sandbox for testing, this checkbox should be checked.  When checked, all campaign pages will be served as secure (https) pages.

**Test Mode**

Check this checkbox if you are using a test Authorize.Net account; otherwise it should be unchecked.


== Screenshots ==

1. Create Personal Fundraiser Screen.
2. Optional user registration.
3. Add outside donations from admin.

== Changelog ==

= 0.7.9 =
* Added support for Authorize.Net thanks to the work of Justin Carboneau from Exygy.
* Fixed issue of donation listing not displaying donations from edit campaign screen.
* Fixed issue of manual donation not accepting decimal values.
* Fixed issue of bullets appearing with pfund-comments shortcode.
* Fixed issue of warning message when other plugins/themes apply the "the_title" filter without the second parameter.
* Fixed issue of user avatar incorrectly displaying user who published the campaign versus the user who created the campaign.

= 0.7.8 =
* Fixed issue of menu text temporarily changing to name of a new personal fundraising campaign when a user creates one.

= 0.7.7 =
* Fixed issue introduced in 0.7.6 that was not allowing the personal fundraising options to be saved.

= 0.7.6 =

* Support for WordPress 3.3.
* Outside donations can now be added through the campaign edit screen.
* Donations can now be viewed from campaign edit screen.
* Donations can now be downloaded from the campaign edit screen as a CSV file for use in Excel and other spreadsheet programs.
* Usability improvements to login/register dialogs.
* Added pfund-progress-bar shortcode to display a progress bar for the campaign.
* Added options to disable Campaign Listing and Cause Listing pages.
* Added ability to modify what cause a campaign uses.
* Added ability for administrators to modify the number of givers (giver tally) for a campaign.
* Added default goal value for causes.
* Added attribute, "default" to image fields so that a default image can be used when a user doesn't include one.
* Added attribute, "max_givers" to pfund-giver-list shortcode to limit number of givers displayed.
* Changed filter 'pfund-transaction-array' to pfund_transaction_array for better naming convention.
* Added "pfund_login_javascript_function" filter to allow a custom javascript function to be called when user chooses to login.
* Added "pfund_register_javascript_function" filter to allow a custom javascript function to be called when user chooses to register.

= 0.7.5 =

* Fixed issue of new sample causes being created everytime personal fundraiser
options are updated.
* Changed comments to correctly go through proper comment filtering.
* Changed action, pfund-add-gift, to pfund_add_gift for consistency.
* Added filters: pfund_field_types, pfund_<CUSTOMTYPE>_input, pfund_<CUSTOMTYPE>_shortcode and pfund_render_field_list_item to allow customization of plugin with custom field types.
* Added shortcode, pfund-giver-list, to display the list of supporters for the current campaign.
* Added shortcode, pfund-user-avatar, to display the avatar of the user who created the campaign.
* Added shortcode, pfund-campaign-permalink, to display the permalink(URL) for the campaign.
* Fixed incorrect subject on email sent when a campaign goal is met.
* Fixed issue with image upload.
* Fixed issue with title and url getting out of sync when editing new campaign from cause screen.

= 0.7.4 =

* Removed debugging information when processing PayPal transaction.

= 0.7.3 =

* Fixed DateTime issue with certain versions of PHP.
* Fixed issue with published email being sent every time an administrator updates a campaign.
* Added logic to send emails using the proper contact information for a campaign.  If the campaign has a user display name and user email field, use those values instead of the post author's contact information.  This is necessary for use cases where the campaign is created by an administrator, but the notifications should be sent to another contact.
* Added action, pfund-add-gift, to allow gifts from other ecommerce solutions to be processed.
* Fixed donate button and giver tally to display on campaign creation screen.
* Added a thank you popup when a donation is received.
* Added formatting to dollar amounts when displayed.
* Changed default user role required to create campaigns to administrator.
* Mailchimp integration now uses display name vs first name and last name.
* Added sample cause and sample PayPal donate button for demonstration purposes.

= 0.7.2 =

* Cleaned up PHP notice messages.
* Added date format option.
* Added end date field.
* Added pfund-days-left shortcode.

= 0.7.1 =

* Fixed Use MailChimp option not properly updating.
* Fixed PayPal to not always use PayPal sandbox (now configurable).
* Added pfund-giver-tally shortcode.

= 0.7 =

* Initial public release

== Upgrade Notice ==

= 0.7.7 =
This version fixes a critical install issue introduced in version 0.7.6.  If you are using 0.7.6, please upgrade.
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
* **[pfund-cause-list]** Display the list of causes.
* **[pfund-comments]** Display the comments/donations for the campaign.
* **[pfund-donate]** PayPal donate button.  This shortcode is required in order to accept donations for the campaigns.
* **[pfund-edit]** Required shortcode to display edit button.
* **[pfund-camp-title]** The title of the campaign.
* **[pfund-days-left]** The number of days left in the campaign (if an end date is specified for the campaign).
* **[pfund-gift-goal]** The amount that the user hopes to raise for their campaign.
* **[pfund-gift-tally]** The total amount raised.
* **[pfund-giver-tally]** The total number of unique donors to the campaign.

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
        * **Campaign URL slug ** The field used to contain the campaign url suffix.  This field is required and the type cannot be changed.
        * **End Date **  The field used to contain the campaign end date.  The type of this field cannot be changed.
        * **Large Text Input (textarea) ** Provides a large textbox for users to provide text.
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
 
Using PayPal requires a Premier or Business PayPal account.  Also there two fields on the Personal Fundraising settings screen that must be set.  These values can be obtained by logging into PayPal:

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
1. Click Website Payment Preferences in the Seller Preferences column. 
1. Make sure that Auto Return for Website Payments is turned on.
1. For Return URL, specify your site's URL. The plugin will override this value to return to the campaign once a donation is processed.
1. Under the Payment Data Transfer (optional) section, make sure that Payment Data Transfer is On.  If it is already on, copy the Identity Token value and paste it into the *Payment Data Transfer Token* field.  If it is not on, turn it on, click on Save at the bottom of the page and then click Website Payment Preferences in the Seller Preferences column to display the screen again with the  Identity Token value.

**Use PayPal Sandbox**
If you are using PayPal's developer sandbox for testing, check this checkbox; otherwise leave it unchecked.

== Screenshots ==

1. Create Personal Fundraiser Screen.
2. Optional user registration.

== Changelog ==

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

== To Do ==

* Reporting on all campaigns
* Finish progress bar shortcode
* Tweak UI


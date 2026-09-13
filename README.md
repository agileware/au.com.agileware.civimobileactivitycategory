# CiviMobile Activity Category (au.com.agileware.civimobileactivitycategory)

[CiviMobile](https://civimobile.org) by default will display **all** Activity Types in the mobile app. This presents the user with **too many options** that are **not relevant** and is often cause for **confusion**.

This extension limits the Activities shown in the CiviMobile app to only those Activities which have the Activity Category, _CiviMobile_.

When this extension is installed (or enabled), a new Option Group, _Activity Category_, is created, along with a new Option Value in that group, _CiviMobile_. You can then control which Activity Types (and, optionally, Activity Statuses) are shown in the mobile app by tagging them with the _CiviMobile_ Activity Category.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## How it works

* An `Activity Category` field (a multi-select `grouping` value) is added to the **Activity Type** and **Activity Status** option editing forms (`Administer > Option Lists > Activity Types` / `Activity Statuses`). This is the field administrators use to categorise individual Activity Types/Statuses.
* When the CiviMobile app requests the list of Activity Types (an `OptionValue.get` API call made with a `civimobile` request parameter), the extension intercepts the request and filters the results down to only those Activity Types whose Activity Category includes _CiviMobile_, sorted by weight. Requests made outside of the CiviMobile app (i.e. without the `civimobile` parameter) are unaffected — normal CiviCRM screens continue to show all Activity Types.

## Usage

1. Go to `Administer > Option Lists > Activity Types` and edit an Activity Type.
2. Set the **Activity Category** field to _CiviMobile_ to have that Activity Type appear in the CiviMobile app. Leave it unset (or choose a different category) to keep it hidden from the app.
3. Optionally, do the same on `Administer > Option Lists > Activity Statuses` to categorise Activity Statuses in the same way.
4. No further action is required — the CiviMobile app will only offer the categorised Activity Types the next time it fetches the list.

## Special configuration requirements

* This extension has no settings page, and requires no API keys, credentials, or CiviCRM permissions beyond the standard access needed to edit Option Values (`administer CiviCRM` / access to Option Lists).
* It **requires** the [com.agiliway.civimobileapi](https://civicrm.org/extensions/civimobileapi) CiviCRM extension to be installed, as declared in `info.xml`. It is intended to be used alongside the [CiviMobile](https://civimobile.org) mobile app.
* On install/enable, the extension automatically creates the _Activity Category_ Option Group and _CiviMobile_ Option Value if they do not already exist — no manual setup of these is required.

## Requirements

* CiviCRM 5.51+
* [com.agiliway.civimobileapi](https://civicrm.org/extensions/civimobileapi) CiviCRM extension

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin
Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

# About the Authors

This CiviCRM extension was developed by the team at
[Agileware](https://agileware.com.au).

[Agileware](https://agileware.com.au) provide a range of CiviCRM services
including:

* CiviCRM migration
* CiviCRM integration
* CiviCRM extension development
* CiviCRM support
* CiviCRM hosting
* CiviCRM remote training services

Support your Australian [CiviCRM](https://civicrm.org) developers, [contact
Agileware](https://agileware.com.au/contact) today!


![Agileware](logo/agileware-logo.png)

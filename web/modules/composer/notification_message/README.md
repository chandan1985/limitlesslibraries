INTRODUCTION
------------

The Notification Message module allows for broadcasting notification messages throughout the site. Notification messages can be broken into different types to support multiple use cases. Some common use cases could be a site needing a global outage message to be displayed at a certain date and time, or maybe a notification message needs to be shown to a subset of users based on a given role.

 Notification messages are aware of entities that are exposed on the route. These entities are used along with the condition API that Drupal core provides. Conditions can be created to support custom use cases if needed.

#### Module Features

 - Create notification message types.
 - Use conditions to show/hide notification messages.
 - Easily attached custom fields to a notification message type.
 - Publish/unpublish notification messages using start and end dates.

REQUIREMENTS
------------

No special requirements

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit
   https://www.drupal.org/documentation/install/modules-themes/modules-8
   for further information.

CONFIGURATION
-------------
 * Update the site Regional Settings to have the correct timezone if not already set correctly, `/admin/config/regional/settings`. As this module uses the site timezone when publishing/unpublished notification messages.

 * Configure the user permissions in Administration » People » Permissions:

   - Administer notification message types

     This allows the user to administer the notification structure, such as adding new fields to the entity; along with managing form and view displays.

   - Administer notification message content

     This allows the user to only manage the CRUD operations for the notification message entity.

 * Configure the notification message structure in Administration » Structure » Notification message types:

   The module ships with a default global notification message type. Additional notification message types can be created depending on the site-builders requirements.

   - Create a new notification message type. It's required to add a label on the notification message type edit form. Optionally, you can filter what conditions are shown on the notification message entity edit form, by selecting the allowed data types. In most cases `entity:node` is a valid choice.

   - After the new notification message type has been created, the following can be customized, if desired.

      * The notification message fields.
      * The notification message fields on the form/view displays.

 * Now that you have a notification message type, you can create, edit, and delete notification messages in Administration » Content » Notification Message:

    - Click on `Add notification message` to create a new notification message on the site. If more than one notification message type has been created, select a type from the list (disregard if there is only one type available).

    - Next, you'll be able to select what conditions are required to be met prior to being displayed. The notification message entities are published based on start and end dates, by default the end date is set two days from the current date. This will need to be changed  based on the site-builders requirements.

 * Finally, the notification messages need to be exposed using a block on the Administration » Structure » Block layout:

    - Click `Place Block` in the desired region. Then select the `Notification Messages` block type.

    - On the block edit form, select the notification message display mode, which dictates how the message entity is rendered. Optionally, you can select the allowed notification message types, only the selected message types will be outputted.

    - Feel free to change standard block configurations.

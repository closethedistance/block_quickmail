<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_quickmail_upgrade($oldversion) {
    require_once('upgradelib.php');

    global $DB;

    $result = true;

    $dbman = $DB->get_manager();

    // 1.9 to 2.0 upgrade
    if ($oldversion < 2011021812) {
        // Changing type of field attachment on table block_quickmail_log to text
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'message');

        // Launch change of type for field attachment
        $dbman->change_field_type($table, $field);

        // Rename field timesent on table block_quickmail_log to time
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('timesent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'format');

        // Conditionally launch rename field timesent
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'time');
        }

        // Define table block_quickmail_signatures to be created
        $table = new xmldb_table('block_quickmail_signatures');

        // Adding fields to table block_quickmail_signatures
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '125', null, null, null, null);
        $table->add_field('signature', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('default_flag', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');

        // Adding keys to table block_quickmail_signatures
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_signatures
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_drafts to be created
        $table = new xmldb_table('block_quickmail_drafts');

        // Adding fields to table block_quickmail_drafts
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('mailto', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('attachment', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null);
        $table->add_field('format', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);

        // Adding keys to table block_quickmail_drafts
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_drafts
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table block_quickmail_config to be created
        $table = new xmldb_table('block_quickmail_config');

        // Adding fields to table block_quickmail_config
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursesid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '25', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, '125', null, null, null, null);

        // Adding keys to table block_quickmail_config
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for block_quickmail_config
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // quickmail savepoint reached
        upgrade_block_savepoint($result, 2011021812, 'quickmail');
    }

    if ($oldversion < 2012021014) {
        $table = new xmldb_table('block_quickmail_alternate');

        $field = new xmldb_field('id');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, true, null, null);

        $table->addField($field);

        $field = new xmldb_field('courseid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, null, 'id');

        $table->addField($field);

        $field = new xmldb_field('address');
        $field->set_attributes(XMLDB_TYPE_CHAR, '100', null,
            XMLDB_NOTNULL, false, null, 'courseid');

        $table->addField($field);

        $field = new xmldb_field('valid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED,
            XMLDB_NOTNULL, false, '0', 'address');

        $table->addField($field);

        $key = new xmldb_key('PRIMARY');
        $key->set_attributes(XMLDB_KEY_PRIMARY, array('id'));

        $table->addKey($key);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        foreach (array('log', 'drafts') as $table) {
            // Define field alternateid to be added to block_quickmail_log
            $table = new xmldb_table('block_quickmail_' . $table);
            $field = new xmldb_field('alternateid', XMLDB_TYPE_INTEGER, '10',
                XMLDB_UNSIGNED, null, null, null, 'userid');

            // Conditionally launch add field alternateid
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // quickmail savepoint reached
        upgrade_block_savepoint($result, 2012021014, 'quickmail');
    }

    if ($oldversion < 2012061112) {
        // Restructure database references to the new filearea locations
        foreach (array('log', 'drafts') as $type) {
            $params = array(
                'component' => 'block_quickmail_' . $type,
                'filearea' => 'attachment'
            );

            $attachments = $DB->get_records('files', $params);

            foreach ($attachments as $attachment) {
                $attachment->filearea = 'attachment_' . $type;
                $attachment->component = 'block_quickmail';

                $result = $result && $DB->update_record('files', $attachment);
            }
        }

        upgrade_block_savepoint($result, 2012061112, 'quickmail');
    }
    
    if ($oldversion < 2012061112) {
    	migrate_quickmail_20();
    }
    
    if ($oldversion < 2014042914){

         // Define field status to be dropped from block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('status');

        // Conditionally launch drop field status.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field status to be added to block_quickmail_log.
        $table = new xmldb_table('block_quickmail_log');
        $field = new xmldb_field('failuserids', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');
        $field2 = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'failuserids');
        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
	   
       if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
        // Define field additional_emails to be added to block_quickmail_drafts.
        $table = new xmldb_table('block_quickmail_drafts');
        $field = new xmldb_field('additional_emails', XMLDB_TYPE_TEXT, null, null, null, null, null, 'time');

        // Conditionally launch add field additional_emails.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2014042914, 'quickmail');
    }

    // upgrade schema for version 2.0

    if ($oldversion < 2018040900) {

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_messages
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_messages');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('message_type', XMLDB_TYPE_CHAR, '8', null, XMLDB_NOTNULL, null, null);
        $table->add_field('alternate_email_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('signature_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('body', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('editor_format', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('to_send_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('is_draft', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('send_receipt', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('send_to_mentors', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('is_sending', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('no_reply', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));
        $table->add_key('alternate_email_id', XMLDB_KEY_FOREIGN, array('alternate_email_id'), 'block_quickmail_alt_emails', array('id'));
        $table->add_key('signature_id', XMLDB_KEY_FOREIGN, array('signature_id'), 'block_quickmail_signatures', array('id'));

        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_msg_recips
        /// 
        ///////////////////////////////////////////////

        $table = new xmldb_table('block_quickmail_msg_recips');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('moodle_message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));

        // create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_draft_recips
        /// 
        ///////////////////////////////////////////////

        $table = new xmldb_table('block_quickmail_draft_recips');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '7', null, XMLDB_NOTNULL, null, null);
        $table->add_field('recipient_type', XMLDB_TYPE_CHAR, '6', null, XMLDB_NOTNULL, null, null);
        $table->add_field('recipient_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('recipient_filter', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));

        // create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_msg_ad_email
        /// 
        ///////////////////////////////////////////////

        $table = new xmldb_table('block_quickmail_msg_ad_email');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sent_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));

        // create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_msg_attach
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_msg_attach');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('message_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('path', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('filename', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('message_id', XMLDB_KEY_FOREIGN, array('message_id'), 'block_quickmail_messages', array('id'));

        // create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_alt_emails
        /// 
        ///////////////////////////////////////////////

        $table = new xmldb_table('block_quickmail_alt_emails');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('setup_user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '125', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '125', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('email', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('is_validated', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('setup_user_id', XMLDB_KEY_FOREIGN, array('setup_user_id'), 'user', array('id'));
        $table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));

        // create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        ///////////////////////////////////////////////
        ///
        ///  MODIFY EXISTING TABLE: block_quickmail_signatures
        /// 
        ///////////////////////////////////////////////

        // get existing table
        $signature_table = new xmldb_table('block_quickmail_signatures');
        
        // renaming userid to user_id (for consistency in new version)
        $user_id_field = new xmldb_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // adding required fields for persistent api
        $usermodified_field = new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $timecreated_field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $timemodified_field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        // add soft delete field
        $timedeleted_field = new xmldb_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, '0');

        // add user_id column
        if (!$dbman->field_exists($signature_table, $user_id_field)) {
            $dbman->add_field($signature_table, $user_id_field);
        }

        // add usermodified column
        if (!$dbman->field_exists($signature_table, $usermodified_field)) {
            $dbman->add_field($signature_table, $usermodified_field);
        }

        // add timecreated column
        if (!$dbman->field_exists($signature_table, $timecreated_field)) {
            $dbman->add_field($signature_table, $timecreated_field);
        }

        // add timemodified column
        if (!$dbman->field_exists($signature_table, $timemodified_field)) {
            $dbman->add_field($signature_table, $timemodified_field);
        }

        // add timedeleted column
        if (!$dbman->field_exists($signature_table, $timedeleted_field)) {
            $dbman->add_field($signature_table, $timedeleted_field);
        }

        // make title non null
        $title_field = new xmldb_field('title', XMLDB_TYPE_CHAR, '125', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($signature_table, $title_field);

        // make signature non null
        $signature_field = new xmldb_field('signature', XMLDB_TYPE_TEXT, 'medium', null, XMLDB_NOTNULL, null, null);
        $dbman->change_field_notnull($signature_table, $signature_field);

        // change default on default_flag to 0 (will be handled during creation/update)
        $default_flag_field = new xmldb_field('default_flag', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $dbman->change_field_default($signature_table, $default_flag_field);

        /*
         * Update signature records, update all userid --> user_id
         */
        
        // get all existing records
        $all_signatures = $DB->get_records('block_quickmail_signatures');

        // for each record, copy userid to user_id and update
        foreach ($all_signatures as $signature) {
            $signature->user_id = $signature->userid;
            
            $DB->update_record('block_quickmail_signatures', $signature);
        }

        // drop userid field
        $userid_field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $dbman->drop_field($signature_table, $userid_field);

        // migrate the data from v1 to v2
        // commenting out as this process can take too long through web upgrade
        // migrate_quickmail_v1_to_v2();

        // drop all of the old tables (but keep 'block_quickmail_log' & 'block_quickmail_drafts' for optional migration later)
        foreach (['block_quickmail_alternate'] as $table_name) {
            $table = new xmldb_table($table_name);

            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018040900, 'quickmail');
    }

    // upgrade schema for notifications

    if ($oldversion < 2018051100) {

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_notifs
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_notifs');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('course_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('is_enabled', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('conditions', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('message_type', XMLDB_TYPE_CHAR, '8', null, XMLDB_NOTNULL, null, null);
        $table->add_field('alternate_email_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('subject', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('signature_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('body', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('editor_format', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1');
        $table->add_field('send_receipt', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('send_to_mentors', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('no_reply', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course_id', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id'));
        $table->add_key('user_id', XMLDB_KEY_FOREIGN, array('user_id'), 'user', array('id'));
        $table->add_key('alternate_email_id', XMLDB_KEY_FOREIGN, array('alternate_email_id'), 'block_quickmail_alt_emails', array('id'));
        $table->add_key('signature_id', XMLDB_KEY_FOREIGN, array('signature_id'), 'block_quickmail_signatures', array('id'));

        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_event_notifs
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_event_notifs');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notification_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('model', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('time_delay', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('notification_id', XMLDB_KEY_FOREIGN, array('notification_id'), 'block_quickmail_notifs', array('id'));

        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_rem_notifs
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_rem_notifs');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('notification_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('model', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, null);
        $table->add_field('object_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('max_per_interval', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('schedule_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('last_run_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('next_run_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('is_running', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('notification_id', XMLDB_KEY_FOREIGN, array('notification_id'), 'block_quickmail_notifs', array('id'));
        $table->add_key('schedule_id', XMLDB_KEY_FOREIGN, array('schedule_id'), 'block_quickmail_schedules', array('id'));

        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_schedules
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_schedules');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('unit', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('amount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('begin_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('end_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define field notification_id to be added to block_quickmail_messages.
        $table = new xmldb_table('block_quickmail_messages');
        $notification_id_field = new xmldb_field('notification_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $notification_id_field)) {
            $dbman->add_field($table, $notification_id_field);

            $notification_id_key = new xmldb_key('notification_id', XMLDB_KEY_FOREIGN, array('notification_id'), 'block_quickmail_notifs', array('id'));

            $dbman->add_key($table, $notification_id_key);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018051100, 'quickmail');
    }

    // add allowed_role_ids to alt_emails

    if ($oldversion < 2018081501) {
        
        // Define field status to be added to block_quickmail_log.
        $table = new xmldb_table('block_quickmail_alt_emails');
        $field = new xmldb_field('allowed_role_ids', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'lastname');
        
        // add field if not already existing
        if ( ! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018081501, 'quickmail');
    }

    // add an "has_migrated" field log and drafts tables if they exist

    if ($oldversion < 2018082100) {
        
        foreach (['block_quickmail_log', 'block_quickmail_drafts'] as $table_name) {
            $table = new xmldb_table($table_name);
            
            if ($dbman->table_exists($table)) {
                $field = new xmldb_field('has_migrated', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');

                // add field if not already existing
                if ( ! $dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }

                // Add index to new has_migrated field.
                if ($table_name == 'block_quickmail_log') {
                    $index = new xmldb_index('bloquilog_has_ix', XMLDB_INDEX_NOTUNIQUE, array('has_migrated'));
                    $dbman->add_index($table, $index);
                }
            }
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018082100, 'quickmail');
    }

    if ($oldversion < 2018092500) {

        ///////////////////////////////////////////////
        ///
        ///  CREATE TABLE: block_quickmail_event_recips
        /// 
        ///////////////////////////////////////////////
        
        $table = new xmldb_table('block_quickmail_event_recips');

        // define fields
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('event_notification_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('notified_at', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);

        // define keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // make table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // add mute_time to event notifications table, after time_delay column
        $table = new xmldb_table('block_quickmail_event_notifs');
        $field = new xmldb_field('mute_time', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'time_delay');
        
        // add field if not already existing
        if ( ! $dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018092500, 'quickmail');
    }

    if ($oldversion < 2018092601) {

        $table = new xmldb_table('block_quickmail_event_notifs');
        
        // drop time_delay field :(

        $time_delay_field = new xmldb_field('time_delay');

        if ($dbman->field_exists($table, $time_delay_field)) {
            $dbman->drop_field($table, $time_delay_field);
        }

        // drop mute_time field :(

        $mute_time_field = new xmldb_field('mute_time');

        if ($dbman->field_exists($table, $mute_time_field)) {
            $dbman->drop_field($table, $mute_time_field);
        }

        // add time_delay and mute_time fields as separate units and amounts :)
        
        $time_delay_amount_field = new xmldb_field('time_delay_amount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'model');
        $time_delay_unit_field = new xmldb_field('time_delay_unit', XMLDB_TYPE_CHAR, '10', null, false, false, null, 'time_delay_amount');
        $mute_time_amount_field = new xmldb_field('mute_time_amount', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'time_delay_unit');
        $mute_time_unit_field = new xmldb_field('mute_time_unit', XMLDB_TYPE_CHAR, '10', null, false, false, null, 'mute_time_amount');
        
        // add field if not already existing
        if ( ! $dbman->field_exists($table, $time_delay_amount_field)) {
            $dbman->add_field($table, $time_delay_amount_field);
        }

        // add field if not already existing
        if ( ! $dbman->field_exists($table, $time_delay_unit_field)) {
            $dbman->add_field($table, $time_delay_unit_field);
        }

        // add field if not already existing
        if ( ! $dbman->field_exists($table, $mute_time_amount_field)) {
            $dbman->add_field($table, $mute_time_amount_field);
        }

        // add field if not already existing
        if ( ! $dbman->field_exists($table, $mute_time_unit_field)) {
            $dbman->add_field($table, $mute_time_unit_field);
        }

        // Quickmail savepoint reached.
        upgrade_block_savepoint(true, 2018092601, 'quickmail');
    }

    return $result;
}

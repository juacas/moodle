<?php
global $CFG;

function local_reminder_cron()
{
mtrace("\n========== Checking reminders START ===========");
local_reminder_sendReminders();
mtrace("========== Checking reminders END ===========");
}

function local_reminder_sendReminders()
{
global $CFG, $DB;
$timeformat="%A %d %B %Y a las %T";
$prev=48*3600; // 48 hours

//$cond= '((eventtype="close" AND modulename="feedback") OR (eventtype="due" AND modulename="assignment")) ';
$now = time();

$lastReminder=get_config(NULL, 'REMINDER_LAST_MESSAGES_TIMESTAMP'); 

if (!$lastReminder)
$lastReminder=$now;

$next_events_timestamp=$now+$prev;
$cond= ' (eventtype="close" AND modulename="feedback") ';
$select="$cond" . "AND (timestart<=$next_events_timestamp AND timestart>$lastReminder) ORDER BY timestart ASC";

mtrace("Searching events from ".usertime($lastReminder, $timeformat)." to ".usertime($next_events_timestamp, $timeformat));
mtrace("query:$select");

$events = $DB->get_records_select('event',$select);

if ($events==false)
{
	mtrace('No reminders');
}
else
{
	
	mtrace('Sending '.count($events).' events.');
	foreach ($events as $event)
	{
		// Get course module from instance id.
		$cm = get_coursemodule_from_instance($event->modulename, $event->instance);
		// Get activity url from instance id.
		$event->url = $CFG->wwwroot . '/mod/feedback/view.php?id=' . $cm->id;
		local_reminder_send_event($event);
//		set_config('REMINDER_LAST_MESSAGES_TIMESTAMP', $event->timestart+1); // mark timestamp to avoid reprocess this event if error
	}	
	set_config('REMINDER_LAST_MESSAGES_TIMESTAMP', $next_events_timestamp+1);
}


}
/**
 * Send a message to users depending on the type of event
 * @param unknown_type $event
 */
function local_reminder_send_event($event)
{
global $CFG, $DB;

if (empty($CFG->messaging)) {
	mtrace("WARNING! no messaging system! Use direct email!");
}

   /// General message information.
        $userfrom = get_admin();
        $site     = get_site();
		
		
        /// Get all the users in the course and send them the reminder
        $users =  enrol_get_course_users($event->courseid);
		/// Get the course record to format message variables
		$course = $DB->get_record('course', ['id' => $event->courseid]);
		
		$a = new stdClass();
        foreach ($users as $user) {
			// Set user language as default.
			force_current_language($user->lang);
			// Format the course name.
			$a->coursefullname = format_string($course->fullname, false);
			// Format the event name.
			$a->eventname = format_string($event->name, false);
			$a->description = format_string($event->description, false);
			$a->event = $event;
			$a->sitefullname = format_string($site->fullname, false);
			$a->eventstart = userdate($event->timestart, get_string('strftimedaydatetime', 'langconfig'));
			$a->url = $event->url;

			$subject  = get_string('remindersubject', 'local_reminder',$a);
			$message = get_string('remindermessage', 'local_reminder', $a);
			
        	if (!empty($CFG->messaging))
        	{
        		message_post_message($userfrom, $user, $message, 0, 'direct');
        	} else
        	{
        		email_to_user($user, $userfrom, $subject, $message);
        	} 

        	mtrace('Sent reminder "' . $event->name . '" to user ' . fullname($user) . "\n");
        }
}

?>

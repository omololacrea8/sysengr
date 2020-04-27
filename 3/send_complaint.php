<?php
// Uncomment line 4 for production.
// ==================================
//error_reporting(0);
?>
<?php

	if ( isset($_POST['ajax_processed']) ){

		// System Variables
		$anonymous_sender_email = "anonymous@gmail.com";
		$always_send_to_mails = array( "mustsend1@gmail.com", "mustsend2@gmail.com", "mustsend3@gmail.com" );

		// Connect to db
		$servername = "localhost";
		$username = "root";
		$password = "";
		$db_name = "complaint";

		try {

			$conn = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password);
			// set the PDO error mode to exception
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		} catch(PDOException $e) {

			echo '<script>notify( "SYSTEM ERROR : '.$e->getMessage().'" , "danger" );</script>';
			exit();
		}

		// Start Injecting to Variables
		$subject = ucwords($_POST['subject']);
		if ( isset( $_POST['sender_email'] ) ) { if ( $_POST['sender_email'] != "" ) { $sender_email = strtolower($_POST['sender_email']); } else { $sender_email = strtolower($anonymous_sender_email); } } else { $sender_email = strtolower($anonymous_sender_email); }
		$recipient_email = strtolower($_POST['recipient_email']);
		$message = ucwords($_POST['message']);

		// Make sure variables are not empty
		if ( $subject != "" && $sender_email != "" && $recipient_email != "" && $message != "" & $always_send_to_mails != "" ) {

			try {

				// First insert into the database
				$stmt = $conn->prepare(" INSERT INTO `tbl_complaint` ( `complaint_subject`, `complaint_sender`, `complaint_recipient`, `complaint_message` ) VALUES ( :subject, :sender_email, :recipient_email, :message ) ");
				$stmt->execute([ 'subject' => $subject, 'sender_email' => $sender_email, 'recipient_email' => $recipient_email, 'message' => $message ]);
				$last_complaint_id = $conn->lastInsertId();

				// Send mail to recipient
				if ( system_send_mail ( $sender_email, $recipient_email, $subject, $message ) ) {

					try {

						// Update the db that the mail sent to recipient
						$stmt = $conn->prepare(" UPDATE `tbl_complaint` SET `recipient_sent_status` = :recipient_sent_status  WHERE `tbl_complaint`.`complaint_id` = :complaint_id ");
						$stmt->execute([ 'recipient_sent_status' => 1, 'complaint_id' => $last_complaint_id ]);

						// Send email to always send to_mails
						foreach ( $always_send_to_mails as $value ) {

							if ( system_send_mail ( $sender_email, $value, $subject, $message ) ) {

								#do nothing....

							} else {

								echo '<script>notify( "AN ERROR OCCURED WHILE SENDING MAIL :(" , "danger" );</script>';
								exit();
							}
						}

						// Update the db that the mail sent to "all always send to mails"
						$stmt = $conn->prepare(" UPDATE `tbl_complaint` SET `always_send_to_sent_status` = :always_send_to_sent_status  WHERE `tbl_complaint`.`complaint_id` = :complaint_id ");
						$stmt->execute([ 'always_send_to_sent_status' => 1, 'complaint_id' => $last_complaint_id ]);

						echo '<script>notify( "YOUR COMPLAINT HAS BEEN SUBMITTED SUCCESSFULLY" , "success" );</script>';						

					} catch (Exception $e) {

						echo '<script>notify( "'.$e->getMessage().'" , "danger" );</script>';
					}

				} else {

					echo '<script>notify( "AN ERROR OCCURED WHILE SENDING MAIL :(" , "danger" );</script>';
				}

			} catch (Exception $e) {

				echo '<script>notify( "'.$e->getMessage().'" , "danger" );</script>';
			}

		} else {

			echo '<script>notify( "INCOMPLETE FIELDS ENTERED" , "danger" );</script>';
		}

	} else {

		header("location:./");
	}

	function system_send_mail( $from_email, $to_email, $subject_text, $local_message ) {

		// Main send mail function
		// =======================================================================
			require_once "Mail.php"; // PEAR Mail package
			require_once ('Mail/mime.php'); // PEAR Mail_Mime packge

			$from = $from_email; //Sender's email address
			$to = $to_email; //Email address of the contact your sending to
			$subject = $subject_text; // subject of your email

			$headers = array ('From' => $from,'To' => $to, 'Subject' => $subject);

			$text = ''; // text versions of email.
			$html = $local_message; // html versions of email.

			$crlf = "\n";

			$mime = new Mail_mime($crlf);
			$mime->setTXTBody($text);
			$mime->setHTMLBody($html);

			//do not ever try to call these lines in reverse order
			$body = $mime->get();
			$headers = $mime->headers($headers);

			$host = "localhost"; // all scripts must use localhost
			$username = ""; //  your email address (same as webmail username)
			$password = ""; // your password (same as webmail password)

			$smtp = Mail::factory('smtp', array ('host' => $host, 'auth' => true, 'username' => $username,'password' => $password));

			$mail = $smtp->send($to, $headers, $body);

			if (PEAR::isError($mail)) {

				return false;
			} else {

				return true;
			}
		// =======================================================================
	}
?>
<?php
$recipient_email    = "ronymia111333@gmail.com"; //recepient
$from_email         = "info@mytech4u.com"; //from email using site domain.

if($_POST){
    
    //php validation, exit outputting json string
    if(empty($_POST["sender_name"])){
        print 'Name is too short or empty!';
        exit;
    }
    if(!filter_var($_POST["sender_email"], FILTER_VALIDATE_EMAIL)){ //email validation
        print 'Please enter a valid email!';
        exit;
    }
    if(empty($_POST["phone"])){ //check for valid numbers in phone number field
        print 'Enter only digits in phone number';
        exit;
    }
    if(empty($_POST["subject"])){ //check emtpy subject
        print 'Subject is required';
        exit;
    }
    if(empty($_POST["message"])){ //check emtpy message
        print 'Too short message! Please enter something.';
        exit;
    }

    $sender_name    = filter_var($_POST["sender_name"], FILTER_SANITIZE_STRING); //capture sender name
    $sender_email   = filter_var($_POST["sender_email"], FILTER_SANITIZE_STRING); //capture sender email
    $phone_number   = filter_var($_POST["phone"], FILTER_SANITIZE_NUMBER_INT);
    $subject        = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
    $message        = filter_var($_POST["message"], FILTER_SANITIZE_STRING); //capture message

    $attachments = $_FILES['my_files'];
    
    $file_count = count($attachments['name']); //count total files attached
    $boundary = md5("sanwebe.com"); 
    
    //construct a message body to be sent to recipient
    $message_body =  "Message from $sender_name\n";
    $message_body .=  "------------------------------\n";
    $message_body .=  "$message\n";
    $message_body .=  "------------------------------\n";
    $message_body .=  "$sender_name\n";
    $message_body .=  "$sender_email\n";
    $message_body .=  "$phone_number\n";
    
    if($file_count > 0){ //if attachment exists
        //header
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "From:".$from_email."\r\n"; 
        $headers .= "Reply-To: ".$sender_email."" . "\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n"; 
        
        //message text
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
        $body .= chunk_split(base64_encode($message_body)); 

        //attachments
        for ($x = 0; $x < $file_count; $x++){       
            if(!empty($attachments['name'][$x])){
                
                if($attachments['error'][$x]>0) //exit script and output error if we encounter any
                {
                    $mymsg = array( 
                    1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
                    2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 
                    3=>"The uploaded file was only partially uploaded", 
                    4=>"No file was uploaded", 
                    6=>"Missing a temporary folder" ); 
                    print  $mymsg[$attachments['error'][$x]]; 
                    exit;
                }
                
                //get file info
                $file_name = $attachments['name'][$x];
                $file_size = $attachments['size'][$x];
                $file_type = $attachments['type'][$x];
                
                //read file 
                $handle = fopen($attachments['tmp_name'][$x], "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $encoded_content = chunk_split(base64_encode($content)); //split into smaller chunks (RFC 2045)
                
                $body .= "--$boundary\r\n";
                $body .="Content-Type: $file_type; name=".$file_name."\r\n";
                $body .="Content-Disposition: attachment; filename=".$file_name."\r\n";
                $body .="Content-Transfer-Encoding: base64\r\n";
                $body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n"; 
                $body .= $encoded_content; 
            }
        }

    }else{ //send plain email otherwise
       $headers = "From:".$from_email."\r\n".
        "Reply-To: ".$sender_email. "\n" .
        "X-Mailer: PHP/" . phpversion();
        $body = $message_body;
    }
        
    $sentMail = mail($recipient_email, $subject, $body, $headers);
    if($sentMail) //output success or failure messages
    {       
        print 'Thank you for your email';
        exit;
    }else{
        print 'Could not send mail! Please check your PHP mail configuration.';  
        exit;
    }
}



?>



















<form enctype="multipart/form-data" method="POST" action="">
    <label>Your Name <input type="text" name="sender_name" /> </label> 
    <label>Your Email <input type="email" name="sender_email" /> </label> 
    <label>Subject <input type="text" name="subject" /> </label> 
    <label>Message <textarea name="message"></textarea> </label> 
    <label>Phone <input type="tel" name="phone" /> </label> 
    <label>Attachment <input type="file" name="my_files[]" multiple/></label>
    <label><input type="submit" name="button" value="Submit" /></label>
</form>
<?php

/**
 * The class represents a website, which contains a collection of pages
 * made within the editor
 */
class Website{

    private $account_id; //who created the page
    private $schema; //the database schema belonging to the site
    private $website_id; //id of the siite
    private $path; //path to page files
    private $name; //site title

    //initalize a Website obeject
    function __construct($account_id, $website_id, $schema, $path, $name){
        $this->account_id = $account_id;
        $this->website_id = $website_id;
        $this->schema = $schema;
        $this->path = $path;
        $this->name = $name;
    }


    /**
     * Attempts to insert a new website into the database and create the 
     * related directories
     * 
     * @param int $accountId the user who owns the site
     * 
     * @return int #website_id the new site ID
     */
    public static function createWebsite($accountId, $path, $siteName, $description){
        // Insert website data
        $siteName = str_replace(" ","_",$siteName);

        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM websites WHERE account_id=? AND site_name=?");
        $stmt->execute([$accountId, $siteName]);
        
        if($stmt->rowCount()){
            return "duplicate";
            die;
        }

        $stmt = Dbh::connect()
            ->PREPARE('INSERT INTO websites(account_id, path, site_name, description, image) VALUES(:accountId, :path, :siteName, :description, :image)');
        $stmt->bindValue(':accountId', $accountId);
        $stmt->bindValue(':path', $path);
        $stmt->bindValue(':siteName',$siteName);
        $stmt->bindValue(':description',$description);
        $stmt->bindValue(':image',"https://images.unsplash.com/photo-1528557692780-8e7be39eafab?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80");
        $stmt->execute();

        // Gather Schema data from website data
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM websites WHERE account_id=? AND site_name=?");
        $stmt->execute([$accountId, $siteName]);
        if($stmt->rowCount()){
            $row = $stmt->fetch();
            $websiteId = array("id"=>$row['website_id']);
        }else{
            return false;
        }

        //Create Schema
        $schemaName = 'website'.$row['website_id'];
        $schemaStmt = Dbh::connect()
            ->PREPARE('CREATE SCHEMA '.$schemaName);
        $schemaStmt->execute();


        $schemaUsers= $schemaName.".users";
        $schemaPages= $schemaName.".pages";

        //Create pages and users tables
        $schemaStmt = Dbh::connect()
            ->PREPARE('CREATE TABLE '.$schemaUsers.'(
            user_id SERIAL PRIMARY KEY NOT NULL,
            first_name text NOT NULL,
            last_name text NOT NULL,
            password varchar(255) NOT NULL,
            email text NOT NULL,
            user_type text NOT NULL)');
        $schemaStmt->execute();

        $schemaStmt = Dbh::connect()
            ->PREPARE('CREATE TABLE '.$schemaPages.'(
            pages_id SERIAL PRIMARY KEY NOT NULL,
            name text NOT NULL,
            path text UNIQUE NOT NULL,
            file text NOT NULL)');
        $schemaStmt->execute();


        // ADD PAGE to new schema page table
        $file = '[{"id":2,"type":"spacer","text":"heading 1","style":[{"backgroundColor":"#FFF","fontSize":"13px","textAlign":"left"}]},{"id":1,"type":"heading","text":"Your Homepage","style":[{"backgroundColor":"#FFF","color":"black","fontSize":"10vh","textAlign":"center","fontFamily":"\"Lucida Sans Unicode\", \"Lucida Grande\", sans-serif","marginBottom":"0px"}]},{"id":3,"type":"image","text":"alt text here","url":"https://images.unsplash.com/photo-1528557692780-8e7be39eafab?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80","style":[{"width":"1150px","borderRadius":"5px","marginLeft":"0","marginRight":"0","marginTop":"0","marginBottom":"0","textAlign":"center","backgroundColor":"#FFF"}]},{"id":4,"type":"spacer","text":"heading 1","style":[{"backgroundColor":"#ffffff","fontSize":"12px","textAlign":"left"}]},{"id":5,"type":"heading","text":"Add videos and pictures to express the unique culture of your company","style":[{"backgroundColor":"#FFF","color":"black","fontSize":"38px","textAlign":"center","fontFamily":"\"Lucida Sans Unicode\", \"Lucida Grande\", sans-serif","marginBottom":"0px"}]},{"id":6,"type":"spacer","text":"heading 1","style":[{"backgroundColor":"#FFF","fontSize":"12px","textAlign":"left"}]},{"id":7,"type":"video","text":"heading 1","url":"https://youtu.be/X4Q7d0CtYyk","style":[{"backgroundColor":"#FFF","fontSize":"10vh","textAlign":"center","height":"500px","width":"750px","margin":"auto","autoplay":"0","loop":"0"}]},{"id":9,"type":"divider","text":"rounded divider","style":[{"borderTop":"8px solid #000000","borderRadius":"0px","width":"100%","backgroundColor":"#ffffff","margin":"0px"}]},{"id":10,"type":"heading","text":"Create custom buttons","style":[{"backgroundColor":"#FFF","color":"black","fontSize":"43px","textAlign":"center","fontFamily":"\"Lucida Sans Unicode\", \"Lucida Grande\", sans-serif","marginBottom":"0px"}]},{"id":11,"type":"button","text":"Your Button","sectionBg":"#FFF","href":"#","style":[{"color":"#FFF","backgroundColor":"#c71a1a","textAlign":"center","border":"0px","borderRadius":"17px","width":"180px"}]},{"id":8,"type":"divider","text":"rounded divider","style":[{"borderTop":"8px solid #000000","borderRadius":"0px","width":"100%","backgroundColor":"#FFF","margin":"0px"}]},{"id":12,"type":"heading","text":"Get started by using our editor!","style":[{"backgroundColor":"#FFF","color":"black","fontSize":"40px","textAlign":"center","fontFamily":"\"Lucida Sans Unicode\", \"Lucida Grande\", sans-serif","marginBottom":"0px"}]}]';

        $stmt = Dbh::connect()
            ->PREPARE("INSERT INTO $schemaPages(name, file, path) VALUES(:name, :file, :path)");
        $stmt->bindValue(':name', "Home");
        $stmt->bindValue(':file', $file);
        $stmt->bindValue(':path',"sites/".$accountId."/".$siteName."/html/home.html" );
        $stmt->execute();
        //Check to see if page is in DB
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schemaPages WHERE path=?");
        $stmt->execute(["sites/".$accountId."/".$siteName."/html/home.html"]);
        if(!$stmt->rowCount()){
            return false;
        }

        //Create backend directory and home page
//        mkdir("../sites/".$accountId);
        mkdir("../sites/".$accountId."/".$siteName, 0701, true);
        mkdir("../sites/".$accountId."/".$siteName."/html", 0701, true);
        mkdir("../sites/".$accountId."/".$siteName."/css", 0701, true);
        mkdir("../sites/".$accountId."/".$siteName."/js", 0701, true);
        $file = fopen("../sites/".$accountId."/".$siteName."/html/home.html","w");
        $txt = "<!DOCTYPE html>
                    <html>
                    <head>
                    <title>Home</title>
                    </head>
                    <body>
                    <h1 style=\"color:black;font-size:81px;text-align:center;font-family:Lucida Sans Unicode,Lucida Grande,sans-serif;\">Your Homepage</h1>
                    
                    <div style=\"color:black;font-size:13px;text-align:left;\" ></div>
                    
                    
                    <div style=\"text-align:center\">
                    <img style=\"width:px;border-radius:5px;margin-left:0;margin-right:0;margin-top:0;margin-bottom:0;text-align:center\" src=\"https://images.unsplash.com/photo-1528557692780-8e7be39eafab?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1350&q=80\" alt=\"alt text here\" />
                    </div>
                    
                    <div style=\"color:black;font-size:12px;text-align:left;\" ></div>
                    
                    <h1 style=\"color:black;font-size:38px;text-align:center;font-family:Lucida Sans Unicode,Lucida Grande,sans-serif;\">Add videos and pictures to express your company's unique culture</h1>
                    
                    <div style=\"color:black;font-size:12px;text-align:left;\" ></div>
                    
                    <div style=\"color:black;font-size:10vh;text-align:center;height:500px;width:750px;margin:auto;\">
                    
                    <iframe width=\"750px\" height=\"500px\" src=\"https://www.youtube.com/embed/X4Q7d0CtYyk\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen></iframe>
                    
                    </div>
                    
                    <hr style=\"border-top:8px solid #000000;border-radius:0px;width:100%\" />
                    
                    <h1 style=\"color:black;font-size:43px;text-align:center;font-family:Lucida Sans Unicode,Lucida Grande,sans-serif;\">Create custom buttons</h1>
                    
                    <div style=\"text-align:center;\">
                    <a className=\"btn btn-primary\"
                    href=\"#\"
                    style=\"color:#000000;background-color:#696969;text-align:center;border:0px;border-radius:12px\">
                    Your Button</a>
                    </div>
                    
                    <hr style=\"border-top:8px solid #0a0606;border-radius:0px;width:100%\" />
                    
                    
                    <h1 style=\"color:black;font-size:40px;text-align:center;font-family:Lucida Sans Unicode,Lucida Grande,sans-serif;\">Get started by using our editor!</h1>
                    </body>
                    </html>
                    ";
        fwrite($file, $txt);
        fclose($file);
        return $websiteId;
    }

    /**
     * Deletes a website and related data
     * 
     * @param int $accountId the user who owns the site
     * @param int $websiteId the user who owns the site
     * 
     * @return boolean if the website was removed
     */
    public static function deleteWebsite($accountId, $websiteId){
        //Gather website name for deletion
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM websites WHERE account_id=? AND website_Id=?");
        $stmt->execute([$accountId, $websiteId]);
        if($stmt->rowCount()){
            $row = $stmt->fetch();
            $siteName = $row['site_name'];
        }else{
            return false;
        }

        //Delete website from websites table
        $stmt = Dbh::connect()
            ->PREPARE('DELETE FROM websites WHERE account_id=? AND website_Id=?');
        $stmt->execute([$accountId, $websiteId]);

        $schemaName = 'website'.$websiteId;

        //DELETE SCHEMA AND ALL TABLES
        $stmt = Dbh::connect()
            ->PREPARE('DROP SCHEMA IF EXISTS '.$schemaName.' CASCADE');
        $stmt->execute();


        //Delete backend directory
        array_map('unlink', glob("../sites/".$accountId."/".$siteName."/html/*.*"));

        rmdir("../sites/".$accountId."/".$siteName."/html");
        rmdir("../sites/".$accountId."/".$siteName."/css");
        rmdir("../sites/".$accountId."/".$siteName."/js");
        return rmdir("../sites/".$accountId."/".$siteName);
    }

    //********************* FUNCTIONS FOR WEBSITE USERS *****************************


    /**
     * Retrieve user from website by arguments
     * 
     * @param string $schema the schema the user belongs to
     * @param string $email the email of the user
     * @param string $psword the password of the user
     * 
     * @return Account[]|boolean user or false if no user
     */
    public static function getUsersByLogin($schema, $email, $psword){
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.users WHERE email=? AND password=?");
        $stmt->execute([$email, $psword]);

        if($stmt->rowCount()){
            while ($row = $stmt->fetch()){
                return $row;
            }
        }else{
            return "Incorrect credentials!";
        }
    }

    /**
     * Retrieve all users from website
     * 
     * @param string $schema the schema the user belongs to
     * 
     * @return Account[]|boolean user list or false if no users
     */
    public static function getAllUsers($schema){
        $stmt = Dbh::connect()
            ->query("SELECT * FROM  $schema.users");
        $users = array();
        if($stmt->rowCount()){
            while ($row = $stmt->fetch()){
                $data = array("firstName"=>$row['first_name'],"lastName"=>$row['last_name'],"email"=>$row['email'],"type"=>$row['user_type'], "id"=>$row['user_id']);
                $users[] = $data;
            }
            return $users;
        }else{
            return false;
        }
    }

    /**
     * Retrieve user from website by arguments
     * 
     * @param string $schema the schema the user belongs to
     * @param string $firstName the first name of the user
     * @param string $lastName the  last name of the user
     * @param string $password the password of the user
     * @param string $email the email of the user
     * @param string $userType the type of the user
     * 
     * @return boolean if the website was removed
     */
    public static function addUser($schema, $firstName, $lastName, $password, $email, $userType){
        $stmt = Dbh::connect()
            ->PREPARE("INSERT INTO $schema.users(first_name, last_name, password, email, user_type) VALUES(:firstName, :lastName, :password, :email, :userType)");
        $stmt->bindValue(':firstName', $firstName);
        $stmt->bindValue(':lastName', $lastName);
        $stmt->bindValue(':password',$password);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':userType', $userType);

        $stmt->execute();

        //Check to see if user is in DB
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.users WHERE email=? AND password=?");
        $stmt->execute([$email, $password]);
        if($stmt->rowCount()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Deletes a user from the database
     * 
     * @param string $schema the schema the user belongs to
     * @param string $userId the ID of the user

     * 
     * @return boolean if the user was removed
     */
    public static function deleteUserById($userId, $schema){
        $stmt = Dbh::connect()->PREPARE("DELETE FROM $schema.users WHERE user_id=?");
        $stmt->execute([$userId]);

        //Check to see if user is in DB
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.users WHERE user_id=?");
        $stmt->execute([$userId]);
        if($stmt->rowCount()){
            return false;
        }else{
            return true;
        }
    }

    //********************* FUNCTIONS FOR WEBSITE PAGES *****************************

    /**
     * Gets all pages for a website 
     * 
     * @param string $schema the schema the pages belong to
     * 
     * @return array|boolean page list or false if no pages
     */
    public static function getAllPages($schema){
        $stmt = Dbh::connect()
            ->query("SELECT * FROM  $schema.pages");
        $pages = array();
        if($stmt->rowCount()){
            while ($row = $stmt->fetch()){
                $data = array("title"=>$row['page_name'], "id"=>$row['pages_id']);
                $pages[] = $data;
            }
            return $pages;
        }else{
            return false;
        }
    }

    /**
     * Gets all pages for a website 
     * 
     * @param string $schema the schema the pages belong to
     * 
     * @return array|boolean page list or false if no pages
     */
    public static function getAllPagesJSON($schema){
        $stmt = Dbh::connect()
            ->query("SELECT * FROM  $schema.pages ORDER BY pages_id ASC");
        $pages = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $pages[] = $row;
        }
        return $pages;
    }

    /**
     * Gets a page by idea
     * 
     * @param string $schema the schema the pages belong to
     * @param int $page_id the ID to get
     * 
     * @return array|string page data or false if no page
     */
    public static function getPagesByPageId($schema, $page_id){
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.pages WHERE pages_id=?");
        $stmt->execute([$page_id]);

        if($stmt->rowCount()){
            while ($row = $stmt->fetch()){
                return $row;
            }
        }else{
            return false;
        }
    }

    /**
     * Adds a page to the database if a page with the same name
     * does not exist already
     * 
     * @param string $schema the schema the pages belong to
     * @param string $pageName the name to add
     * @param int the website to add the page to
     * @param int the user who created the page
     * 
     * @return array|boolean the new page path and ID, or false on failure
     */
    public static function addPage($schema, $pageName, $siteId,$accountId){
        $pageName = trim($pageName);

        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.pages WHERE name=?");
        $stmt->execute([$pageName]);
        if($stmt->rowCount()){
            return 'duplicate';
            die;
        }
            
        //Get website name
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM websites WHERE website_id=?");
        $stmt->execute([$siteId]);
        if($stmt->rowCount()){
            $row = $stmt->fetch();
            $websiteId = array("name"=>$row['site_name']);
        }else{
            return false;
        }
        $siteName = $websiteId["name"];

        // ADD PAGE to new schema page table
        $path = "sites/".$accountId."/".$siteName."/html/".$pageName.".html";

        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.pages WHERE path=?");
        $stmt->execute([$path]);
        if($stmt->rowCount()){
            return 'duplicate';
            die;
        }

        $stmt = Dbh::connect()
            ->PREPARE("INSERT INTO $schema.pages(name, file, path) VALUES(:name, :file, :path)");
        $stmt->bindValue(':name', $pageName);
        $stmt->bindValue(':file', json_encode([]));
        $stmt->bindValue(':path', $path);
        $stmt->execute();

        //Add page in server under /sites/"websiteName"/html/"pageName"
        $file = fopen("../".$path,"w");
        fwrite($file, "");
        fclose($file);

        //Check to see if page is in DB
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.pages WHERE path=?");
        $stmt->execute([$path]);
        if($stmt->rowCount()){
            return [$stmt->fetch(PDO::FETCH_ASSOC)['pages_id'], $path];
        }else{
            return false;
        }
    }

    /**
     * Deletes a page from a website
     * @param string $schema the schema the pages belong to
     * @param int $pageId the page to delete
     * @param string path to the page HTML
     * 
     * @return boolean if the page was deleted
     */
    public static function deletePageById($schema, $pageId, $path){
        //delete pages table entry
        $stmt = Dbh::connect()->PREPARE("DELETE FROM $schema.pages WHERE pages_id=?");
		$stmt->execute([$pageId]);

        //delete html file
        $htmlFileDeleted = unlink("../".$path);

        //Check to see if user is in DB
        $stmt = Dbh::connect()
            ->PREPARE("SELECT * FROM $schema.pages WHERE pages_id=?");
        $stmt->execute([$pageId]);
        if($stmt->rowCount() || !$htmlFileDeleted){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Save a page being changed in the editor
     * @param string $schema the schema the pages belong to
     * @param string $pageId the page to delete
     * @param array $page page elements
     * @param string $path path to the page HTML
     * @param string $html the page's HTML to be written
     */
    public static function savePage($schema, $pageId, $page, $path, $html){
        $stmt = Dbh::connect()
            ->PREPARE("UPDATE $schema.pages SET file =? WHERE pages_id=?");
        $stmt->execute([$page, $pageId]);

        if(!empty($path)){
            $file = fopen("../".$path,"w");
            fwrite($file, $html);
            fclose($file);
        }

    }
}
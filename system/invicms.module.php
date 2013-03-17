<?php
/**
 * Main class of inviCMS. 
 *
 * @author Andrey "SkaN" Kamozin <andreykamozin@gmail.com>
 */
class inviCMS {
    /**
     * Method inserts your content into main template and prints it
     * 
     * @param string $title Title of current page
     * @param string $content Content, which should insert into template
     * 
     * @return void
     */
    public static function out($title, $content)
    {
        // Init templater
        $templater = new inviTemplater();
        
        // Load page template
        $templater->load("main");
        
        // Prepare params
        $params = array(
            'title' => $title,
            'content' => $content
        );
        
        // Parse & print
        print( $templater->parse( $params ) );
    }
}

?>

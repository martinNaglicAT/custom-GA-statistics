<?php  
    //PHP data retrieval for Google Analytics

    //generic values
    $fT = ""; 
    $forumParentTitle = ""; 
    $forumGrandPTitle = ""; 

    $categoryName = "";
    $categoryParentName = "";

    $category_names_json = '[]'; // An empty JSON array as a placeholder
    $category = "";


    //meat

    if(is_bbpress()){
        //data retreival on forums
        $bbPress = "bbpress yes";

        $forumID = bbp_get_forum_id();
        $forumTitle = bbp_get_forum_title();


        if(!empty($forumID)){

            $fT = $forumTitle;
            $forumParentID = bbp_get_forum_parent_id($forumID);

            if(!empty($forumParentID)){

                $forumParentTitle = get_the_title($forumParentID);
                $forumGrandPID = bbp_get_forum_parent_id($forumParentID);

                if(!empty($forumGrandPID)){
                    $forumGrandPTitle = get_the_title($forumGrandPID);
                }

            } 

        } 

    } else {
        //data retreival on portal
        $bbPress = "bbpress no";

        //Here we will put the PHP logic for data retrieval within the portal part - for tracking categories
        //first I need the logic to check if I'm on a post or on a category/sub category archive page

        if (is_category()) {
            $categoryName = single_cat_title('', false);
            $category = get_queried_object();

            
            if ($category instanceof WP_Term) {
                $categoryParent = $category->parent;
                $categoryParentName = ($categoryParent !== 0) ? get_term($categoryParent, 'category')->name : "";
            } else {
                $categoryParent = 0; // Set a default value when $category is not found
                $categoryParentName = ""; // Set a default value when $category is not found
            }
        } /*elseif (is_tax()) {

            //defining php parameters for custom taxonomy archives
            $categoryParentName = "";

        }*/ elseif (is_single()) {

            // Get an array of category IDs associated with the post
            $category_ids = wp_get_post_categories(get_the_ID());

            // Initialize arrays to store category names and parent category names
            $category_names = [];
            $parent_category_names = [];

            foreach ($category_ids as $category_id) {
                // Get the category object for the current category ID
                $category = get_category($category_id);

                // Get the name of the current category
                $category_name = $category->name;

                $category_names[] = $category_name;

                // Check if the current category has a parent
                if ($category->parent !== 0) {
                    // Get the parent category object
                    $parent_category = get_category($category->parent);

                    // Get the name of the parent category
                    $parent_category_name = $parent_category->name;

                    // Store the category names and parent category names in arrays
                    $category_names[] = $parent_category_name;

                    // Now you have the name of the current category and its parent category
                    // You can use these values as needed
                } 


            }

            // Remove duplicate category names
            $category_names = array_unique($category_names);


            // Encode the arrays as JSON
            $category_names_json = json_encode(implode(', ', $category_names));


        } //elseif (/*logic to determine if it's a custom post template within the custom taxonomy archive*/) {

           // $categoryParentName = "";

        /*}*/ /*else {
            //just a spare condition in case I need it in case if I'm on the front page or some other page that doesn't need these custom tracking parameters
            $categoryName = "";
            $categoryParentName = "";
        }*/
    }
?>

<!-- Global site tag (gtag.js) - Google Analytics -->

<script async src="https://www.googletagmanager.com/gtag/js?id=G-S0FK2VC84S"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-S0FK2VC84S');

  const ga4ClientID = gtag('get', 'GA4_CLIENT_ID'); // Retrieve the GA4 client ID

  //console.log(<?php echo json_encode($bbPress); ?>);

    function trackForumPageView() {
        //console.log("trackForumPageView start");
        var forumTitle = <?php echo json_encode($fT); ?>;
        var forumParent = <?php echo json_encode($forumParentTitle); ?>;
        var forumGrandparent = <?php echo json_encode($forumGrandPTitle); ?>;

        if (ga4ClientID) {
            //console.log("trackForumPageView with cookies");
            if (forumTitle !== "") {
              // Track the forum page view with the GA4 client ID
                gtag('event', 'forum_page_view', {
                  'forum_name': forumTitle,
                  'ga4_client_id': ga4ClientID, // Include the GA4 client ID as a custom parameter
                });

                if (forumParent !== "") {
                  // Track the parent forum page view
                  gtag('event', 'forum_page_view', {
                    'forum_name': forumParent,
                    'ga4_client_id': ga4ClientID,
                  });

                  if (forumGrandparent !== "") {
                    // Track the grandparent forum page view
                    gtag('event', 'forum_page_view', {
                      'forum_name': forumGrandparent,
                      'ga4_client_id': ga4ClientID,
                    });
                  }
                }  
            }

        } else {
            //console.log("trackForumPageView without cookies");

            if (forumTitle !== "") {
                // Track the forum page view with the GA4 client ID
                gtag('event', 'forum_page_view', {
                    'forum_name': forumTitle,
                });

                if (forumParent !== "") {
                    // Track the parent forum page view
                    gtag('event', 'forum_page_view', {
                        'forum_name': forumParent,
                    });

                    if (forumGrandparent !== "") {
                        // Track the grandparent forum page view
                        gtag('event', 'forum_page_view', {
                            'forum_name': forumGrandparent,
                        });
                    }
                }
            }

        }
        //console.log("trackForumPageView end");
    }

    function trackCategoriesPageView() {
        //function for WP categories
        console.log("the other function");


        // Retrieve the JSON-encoded category names and parent category names
        const categoryNamesJSON = <?php echo $category_names_json; ?>;
        

        //Retrieve the category name on a category archive page 
        const categoryName = <?php echo json_encode($categoryName); ?>;
        const parentCategoryName = <?php echo json_encode($categoryParentName); ?>;

        if (categoryNamesJSON !== ""){
            const categoryNamesArray = categoryNamesJSON.split(', ');

            for (const categoryName1 of categoryNamesArray) {

                gtag('event', 'category_page_view', {
                  'category_name': categoryName1,
                });

            } 
        } else if (categoryName !== ""){
            gtag('event', 'category_page_view', {
              'category_name': categoryName,
            });

            if (parentCategoryName !== ""){
                gtag('event', 'category_page_view', {
                  'category_name': parentCategoryName,
                });
            }
        } 
         


        //console.log("single cat name: "+categoryName);
        //console.log("single parent name: "+parentCategoryName);
        
    }

  if(window.location.pathname.includes('/forum/')) {

    //forum logic

    trackForumPageView();



  } else {

    trackCategoriesPageView();

  }
</script>
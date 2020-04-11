<?php
/*
Plugin Name: WPDB Demo
Plugin URI:
Description: Demonstration of WPDB Methods
Version: 1.0.0
Author: LWHH
Author URI:
License: GPLv2 or later
Text Domain: wpdb-demo
 */

function wpdbdemo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(250),
			email VARCHAR(250),
            age INT,
			PRIMARY KEY (id)
	);";
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta( $sql );
}

register_activation_hook( __FILE__, "wpdbdemo_init" );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_wpdb-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'wpdb-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'wpdb-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/main.js", array( 'jquery' ), time(), true );
        $nonce = wp_create_nonce( 'display_result' );
        wp_localize_script(
            'wpdb-demo-js',
            'plugindata',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => $nonce )
        );
    }
} );

add_action( 'wp_ajax_display_result', function () {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    if ( wp_verify_nonce( $_POST['nonce'], 'display_result' ) ) {
        $task = $_POST['task'];
        if ( 'add-new-record' == $task ) {
            /* $person = array(
            'name'  => 'John Doe',
            'email' => 'john@doe.com',
            'age'   => 42,
            ); */

            $person2 = array(
                'name'  => 'Jane Doe',
                'email' => 'jane@doe.com',
                'age'   => 42,
            );
            $wpdb->insert( $table_name, $person2, array( '%s', '%s', '%d' ) );
            echo "New Record Added <br/>";
            echo "ID: {$wpdb->insert_id} <br/>";
        } elseif ( 'replace-or-insert' == $task ) {
            /* $person2 = array(
            'id'    => 2,
            'name'  => 'Jimmy Doe',
            'email' => 'jimmy@doe.com',
            'age'   => 24,
            ); */
            $person2 = array(
                'id'    => 3,
                'name'  => 'Jane Doe',
                'email' => 'jane@doe.com',
                'age'   => 42,
            );
            $wpdb->replace( $table_name, $person2 );
            echo "Operation Done <br/>";
            echo "ID: {$wpdb->insert_id} <br/>";
        } elseif ( 'update-data' == $task ) {
            $person = array( 'age' => 29 );
            $result = $wpdb->update( $table_name, $person, array( 'id' => 3 ) );
            echo "Operation Done. Result = {$result} <br/>";
        } elseif ( 'load-single-row' == $task ) {
            $data = $wpdb->get_row( "select * from {$table_name} where id=1" ); //OBJECT
            print_r( $data );

            $data = $wpdb->get_row( "select * from {$table_name} where id=1", ARRAY_A );
            print_r( $data );

            $data = $wpdb->get_row( "select * from {$table_name} where id=1", ARRAY_N );
            print_r( $data );
        } elseif ( 'load-multiple-row' == $task ) {
            $data = $wpdb->get_results( "select * from {$table_name}", ARRAY_A ); //OBJECT
            print_r( $data );

            $data = $wpdb->get_results( "select email, id, name, age from {$table_name}", OBJECT_K );
            print_r( $data );
        } elseif ( 'add-multiple' == $task ) {
            $persons = array(
                array(
                    'name'  => 'David',
                    'email' => 'david@doe.com',
                    'age'   => 30,
                ),
                array(
                    'name'  => 'Brenda',
                    'email' => 'brenda@doe.com',
                    'age'   => 31,
                ),
            );

            foreach ( $persons as $person ) {
                $wpdb->insert( $table_name, $person );
            }

            $data = $wpdb->get_results( "select id, name, email, age from {$table_name}", OBJECT_K );
            print_r( $data );

        } elseif ( 'prepared-statement' == $task ) {
            $id = 2;
            $email = 'john@doe.com';
            //$prepared_statement = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id > %d", $id );
            $prepared_statement = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE email = %s", $email );
            $data = $wpdb->get_results( $prepared_statement, ARRAY_A );
            print_r( $data );
        } elseif ( 'single-column' == $task ) {
            $query = "SELECT email FROM {$table_name}";
            $result = $wpdb->get_col( $query );
            print_r( $result );
        } elseif ( 'single-var' == $task ) {
            $result = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
            echo "Total Users: {$result}<br/>";

            $result = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 0, 0 );
            echo "Name of 1st User: {$result}<br/>";

            $result = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 1, 0 );
            echo "Email of 1st User: {$result}<br/>";

            $result = $wpdb->get_var( "SELECT name, email FROM {$table_name}", 1, 2 );
            echo "Email of 3rd User: {$result}<br/>";
        } elseif ( 'delete-data' == $task ) {
            $result = $wpdb->delete( $table_name, array('email' => 'test@test.com') );
            echo "Delete Result = {$result}";
        }
    }
    die( 0 );
} );

add_action( 'admin_menu', function () {
    add_menu_page( 'WPDB Demo', 'WPDB Demo', 'manage_options', 'wpdb-demo', 'wpdbdemo_admin_page' );
} );

function wpdbdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>WPDB Demo</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='add-new-record'>Add New Data</button>
                        <button class="action-button" data-task='replace-or-insert'>Replace or Insert</button>
                        <button class="action-button" data-task='update-data'>Update Data</button>
                        <button class="action-button" data-task='load-single-row'>Load Single Row</button>
                        <button class="action-button" data-task='load-multiple-row'>Load Multiple Row</button>
                        <button class="action-button" data-task='add-multiple'>Add Multiple Row</button>
                        <button class="action-button" data-task='prepared-statement'>Prepared Statement</button>
                        <button class="action-button" data-task='single-column'>Display Single Column</button>
                        <button class="action-button" data-task='single-var'>Display Variable</button>
                        <button class="action-button" data-task='delete-data'>Delete Data</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

<?php
/*
*   Markers and Students Admin UI
*   -----------------------------
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly





$marking_data       = array();
$assignmentID       = ( isset( $_GET['id'] ) ) ? intval( $_GET['id'] ) : 0;
$assignmentName     = get_the_title($assignmentID);

$assignment         = get_post( $assignmentID );
$group_edit_id      = ( isset( $_GET['group_edit_id'] ) ) ? intval( $_GET['group_edit_id'] ) : 0;
$view               = $group_edit_id ? 'edit_criteria' : '';
$feedback           = '';


$archived = get_post_meta( $assignmentID, 'archived', true );
$useStepScale = get_post_meta( $assignmentID, 'useStepScale', true );

// Check to see if there is any saved data to warn them
$savedMarks = agreedMarkingQueries::getAllAssignmentMarks($assignmentID);

$markedCount = count($savedMarks);




if($archived==true)
{
   $message = 'This assignment has been archived and changes to criteria are not possible.';
   echo agreedMarkingAdminDraw::drawAdminNotice($message, "warning");
}elseif($markedCount>=1)
{
   $message = '<span style="color:red">WARNING!</span><br/>This assignment has saved student marks.<br/>Editing or removing criteria may result in a loss of data.';
   echo agreedMarkingAdminDraw::drawAdminNotice($message, "warning");
}

if($useStepScale==true)
{
   $message = 'This assignment uses the College stepped scale and so custom criteria scales are not possible.';
   echo agreedMarkingAdminDraw::drawAdminNotice($message, "warning");

}




if ( ! empty( $assignment ) ) {

    // Handle _POST submits
    if ( isset( $_POST['save_groups_submit'] ) ) { // Groups page

        agreedMarkingActions::save_groups_data( $assignmentID );
        if ( isset( $_POST['delete_refs'] ) ) {
            agreedMarkingActions::delete_group_data( $_POST['delete_refs'] );
        }
        $feedback = '<p>Groups updated.</p>';

    } elseif ( isset( $_POST['save_criteria_submit'] ) ) { // Criteria page

        agreedMarkingActions::save_criteria_data();
        if ( isset( $_POST['delete_refs'] ) ) {
            agreedMarkingActions::delete_criteria_data( $_POST['delete_refs'] );
        }
        $feedback = '<p>Criteria updated.</p>';
    }

    // Get the marking data
   // $marking_data = agreedMarkingActions::get_marking_data( $assignmentID );

    $marking_data = agreedMarkingQueries::getMarkingCriteriaForAdmin($assignmentID);

}
?>


<div class="wrap">
    <h1>Marking Criteria</h1>
<?php
if ( $assignment ) {

    if ( $feedback ) {
        echo '<div class="updated notice is-dismissible">' . $feedback . '</div>';
    }

    $url_parts = explode( '&', $_SERVER['REQUEST_URI'] );
    $groups_url = admin_url() . 'options.php?page=agreed-marking-criteria&id=' . $assignmentID;
    $assignments_url = admin_url() . 'edit.php?post_type=agreed-marking';
    ?>

    <hr>
    <div class="breadcrumbs">
        <?php
        if ( 'edit_criteria' === $view ) {
            echo '<a href="' . $assignments_url . '">Assignments</a> &gt; <a href="' . $groups_url . '">' . esc_html( $assignment->post_title ) . ' (Criteria Groups)</a> &gt; <span>Criteria</span>';
        } else {
            echo '<a href="' . $assignments_url . '">Assignments</a> &gt; <span>' . esc_html( $assignment->post_title ) . ' (Criteria Groups)</span>';
        }
        ?>
    </div>
    <hr>

    <div id="marking_ui">
        <form id="marking_data" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <?php
    if ( 'edit_criteria' === $view ) {


    // Edit criteria view

        // Check that group exists, and pick the existing criteria
        $is_editable = false;
        $criteria = array();
        $group_index = 0;
        foreach ( $marking_data as $i => $group ) {
            if ( $group_edit_id == $group['groupID'] ) {
                $criteria = $marking_data[ $i ]['criteria'];
                $is_editable = true;
                $group_index = $i;
                break;
            }
        }


        // Draw UI
        if ( $is_editable ) {

            $button_class = count( $criteria ) > 0 ? '' : 'hidden-control';


            $groupName = $marking_data[ $group_index ]['groupName'];
            echo '<h2>'.$groupName.' : Criteria</h2>';
            echo '<div id="help_message" class="message">';
            if ( empty( $criteria ) ) {
                echo '<p>No Criteria found.</p>';
            }
            echo '</div>';
            echo '<div class="button-secondary expand-collapse has-click-event ' . $button_class . '" data-callback="expand_collapse_options" data-is-open="1">Collapse / Expand All</div>';
            echo '<div class="clear"></div>';
            ?>

                <div id="groups_wrap" data-assignment-id="<?php echo $assignmentID; ?>" class="criteria-items">
            <?php
            foreach ( $criteria as $i => $c ) {

                $criteriaID = intval( $c['criteriaID'] );
                $button_class = 'radio'     == $c['criteriaType'] ? '' : 'hidden-control';

                $responseType =  $c['criteriaType'];
                $window_class = '';
                if($responseType=="textarea" || $responseType=="stepScale")
                {
                    $window_class = 'hidden-control';
                }
                ?>
                    <div class="group">
                        <div class="display-order"><?php echo ( $i + 1 ); ?></div>
                        <div class="group-field name">
                            <label>Criteria &nbsp;&nbsp;</label>
                            <input type="text" name="criteria[<?php echo $criteriaID; ?>][criteriaName]" value="<?php echo esc_attr( $c['criteriaName'] ); ?>" />
                            <br><label>Response Type &nbsp;&nbsp;</label>
                            <select name="criteria[<?php echo $criteriaID; ?>][criteriaType]" class="has-change-event" data-callback="change_criteria_type" data-criteria-id="<?php echo $criteriaID; ?>">

                                <?php
                                if($useStepScale=="on")
                                {
                                    ?>
                                    <option value="stepScale"<?php echo ( 'stepScale' === $c['criteriaType'] ? ' SELECTED' : '' ); ?>>Step Scale</option>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <option value="radio"<?php echo ( 'radio' === $c['criteriaType'] ? ' SELECTED' : '' ); ?>>Radio Buttons</option>
                                <?php
                                }
                                ?>

                                <option value="checkbox"<?php echo ( 'checkbox' === $c['criteriaType'] ? ' SELECTED' : '' ); ?>>Check Boxes</option>
                                <option value="textarea"<?php echo ( 'textarea' === $c['criteriaType'] ? ' SELECTED' : '' ); ?>>Text Area</option>
                            </select>
                        </div>
                        <div class="group-field">
                            <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][criteriaOrder]" value="<?php echo intval( $c['criteriaOrder'] ); ?>" class="group-order-field" />
                            <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][criteriaID]" value="<?php echo $criteriaID; ?>" />
                            <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][groupID]" value="<?php echo $group_edit_id; ?>" />
                        </div>

                        <div class="options-window <?php echo $window_class; ?>" id="options_window_<?php echo $criteriaID; ?>">
                            <h2>Responses</h2>

                                <div id="options_wrap_<?php echo $criteriaID; ?>" class="options-wrap">
                                <?php
                                if ( is_array( $criteria[ $i ]['options'] ) ) {
                                    foreach ( $criteria[ $i ]['options'] as $j => $op ) {
                                        $op_id = intval( $op['optionID'] );
                                        ?>
                                        <div class="option">
                                            <div class="option-order"><?php echo ( $j + 1 ); ?></div>
                                            <div class="option-form">
                                               <input type="text" name="criteria[<?php echo $criteriaID; ?>][options][<?php echo $op_id; ?>][optionValue]" value="<?php echo esc_attr( $op['optionValue'] ); ?>" />
                                               <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][options][<?php echo $op_id; ?>][optionOrder]" value="<?php echo intval( $op['optionOrder'] ); ?>" />
                                               <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][options][<?php echo $op_id; ?>][criteriaID]" value="<?php echo $criteriaID; ?>" />
                                               <input type="hidden" name="criteria[<?php echo $criteriaID; ?>][options][<?php echo $op_id; ?>][optionID]" value="<?php echo $op_id; ?>" />
                                           </div>
                                            <div class="remove-option has-click-event" data-callback="remove_option" data-option-id="<?php echo $op_id; ?>" data-criteria-id="<?php echo $criteriaID; ?>" title="Remove Option">x</div>
                                        </div>
                                    <?php
                                    }
                                }
                                ?>
                                </div>
                            <div class="test123">
                            <span class="button-secondary has-click-event" data-callback="add_new_option" data-criteria-id="<?php echo $criteriaID; ?>">+ Add Response Option</span>
                            &nbsp;<span class="button-secondary make-numeric-scale has-click-event <?php echo $button_class; ?>" data-callback="make_numeric_scale" data-criteria-id="<?php echo $criteriaID; ?>">Create 1-10</span>
                            &nbsp;<span class="button-secondary has-click-event" data-callback="launch_options_window" data-criteria-id="<?php echo $criteriaID; ?>">Sort Order</span>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="remove-criteria has-click-event" data-callback="remove_criteria" data-criteria-id="<?php echo $criteriaID; ?>" title="Remove Criteria">Remove</div>
                    </div>
            <?php
            }
            ?>
                </div><!-- #groups_wrap.criteria-items -->

                <?php

                if($archived<>true)
                {
                ?>
                <div id="controls_bar">
                    <span class="button-secondary has-click-event" data-callback="add_new_criteria">+ Add New Criteria</span>
                    <hr/>
                    <input type="submit" name="save_criteria_submit" value="Save Changes" class="button-primary has-click-event" data-callback="pre_criteria_submit" />
                    <a href="options.php?page=agreed-marking-criteria&id=<?php echo $assignmentID;?>" class="button-secondary">Cancel</a>
                </div>
                <?php
             }


        } else {
            echo '<p>Parent Group not found.</p>';
            echo '<p><a href="' . $groups_url . '">Back to Groups</a></p>';
        }


    } else {
    // Default edit groups view

        echo '<h2>'.$assignmentName.' : Criteria Groups</h2>';
        echo '<div id="help_message" class="message">';
        if ( empty( $marking_data ) ) {
            echo '<p>No Criteria Groups have been added yet, click \'Add New Group\' below to start.</p>';
        }
        echo '</div>';
        ?>

            <div id="groups_wrap" data-assignment-id="<?php echo $assignmentID; ?>" class="group-items">
                <?php
                if ( is_array( $marking_data ) && ! empty( $marking_data ) ) {
                    foreach ( $marking_data as $i => $group ) {
                        $groupID = intval( $group['groupID'] );

                        $saved_criteria_ids = array();
                        if ( is_array( $group['criteria'] ) ) {
                            foreach ( $group['criteria'] as $cr ) {
                                $saved_criteria_ids[] = $cr['criteriaID'];
                            }
                        }
                        ?>
                        <div class="group" data-saved-criteria-ids="<?php echo implode( ',', $saved_criteria_ids ); ?>">
                            <div class="display-order"><?php echo ( $i + 1 ); ?></div>
                            <div class="group-field name">
                                <input type="text" name="groups[<?php echo $groupID; ?>][groupName]" value="<?php echo esc_attr( $group['groupName'] ); ?>" />
                                <div class="add-edit"><a href="<?php echo $_SERVER['REQUEST_URI']; ?>&group_edit_id=<?php echo $groupID; ?>" class="button-secondary" >Add / Edit Criteria</a></div>

                            </div>
                            <div class="group-field weighting">
                                <label>Weighting &nbsp;&nbsp;</label>
                                <input type="text" name="groups[<?php echo $groupID; ?>][weighting]" value="<?php echo intval( $group['weighting'] ); ?>" /> %
                            </div>
                            <div class="group-field">
                                <input type="hidden" name="groups[<?php echo $groupID; ?>][groupOrder]" class="group-order-field" value="<?php echo intval( $group['groupOrder'] ); ?>" />
                                <input type="hidden" name="groups[<?php echo $groupID; ?>][groupID]" value="<?php echo $groupID; ?>" />
                            </div>

                            <div class="remove-group has-click-event" data-callback="remove_group" data-group-id="<?php echo $groupID; ?>" title="Remove Group">Remove</div>
                            <div class="clear"></div>
                        </div>
                    <?php
                    }
                }
                ?>
            </div><!-- #groups_wrap.group-items -->
            <?php
            if($archived<>true)
            {

               ?>
               <div id="controls_bar">
                   <span class="button-secondary has-click-event" data-callback="add_new_group">+ Add New Group</span>
                   <input type="submit" name="save_groups_submit" value="Save Changes" class="button-primary has-click-event" data-callback="pre_groups_submit" />
               </div>
    <?php
         }
    }
    ?>

            <div id="deletion_data"></div>
        </form><!-- #marking_data -->
    </div><!-- #marking_ui -->
<?php
} else {
    echo '<p>Assignment not found or invalid.</p>';
}



/*
echo '<pre>_POST:';
print_r( $_POST );
echo '</pre>';
echo '<pre>marking_data:';
print_r( $marking_data );
echo '</pre>';
//*/
?>
</div><!-- .wrap -->

<script>
    var AMARK = {

        new_id:             0,
        ui_wrap_id:         'marking_ui',
        items_wrap_id:      'groups_wrap',
        ajax_url:           '',

        //---
        init: function () {
            this.add_listeners();
        },

        //---
        add_listeners: function () {
            jQuery('#' + AMARK.ui_wrap_id ).on( 'click', '.has-click-event', function ( e ) {
                AMARK.on_ui_event( e );
            });
            jQuery('#' + AMARK.ui_wrap_id ).on( 'change', '.has-change-event', function ( e ) {
                AMARK.on_ui_event( e );
            });
            jQuery('#' + AMARK.items_wrap_id ).sortable({
                update: function( event, ui ) {
                    AMARK.renumber_fields();
                    AMARK.renumber('.display-order');
                }
            });
        },

        //---
        on_ui_event: function ( e ) {
            var elem = e.target;
            var callback = jQuery( elem ).attr('data-callback');
            if ( typeof AMARK[ callback ] !== 'undefined' ) {
                AMARK[ callback ]( e );
            }
        },

/*
*   GROUPS
*/
        add_new_group: function ( e ) {
            var new_id = AMARK.get_new_id();
            var markup = AMARK.new_group( new_id );
            jQuery('#' + AMARK.items_wrap_id ).append( markup );
            AMARK.renumber('.display-order');
            jQuery('input[name="groups[' + new_id + '][groupName]"]').focus();
            jQuery('#help_message').empty();
        },

        //---
        remove_group: function ( e ) {
            var item         = jQuery( e.target ).closest('.group');
            var group_id     = jQuery( e.target ).attr('data-group-id');
            var criteria_ids = jQuery( item ).attr('data-saved-criteria-ids');
            if ( group_id ) {
                AMARK.add_deletion_reference( 'groupID', group_id );
                if ( criteria_ids ) {
                    AMARK.add_deletion_reference( 'criteriaID', criteria_ids );
                }
            }
            jQuery( item ).slideToggle( 300, function () {
                jQuery( item ).remove();
            });
        },

        //---
        pre_groups_submit: function ( e ) {
            AMARK.renumber_fields();
        },

/*
*   CRITERIA
*/
        add_new_criteria: function ( e ) {
            var new_id = AMARK.get_new_id();
            var markup = AMARK.new_criteria( new_id );
            jQuery('#' + AMARK.items_wrap_id ).append( markup );
            AMARK.renumber('.display-order');
            jQuery('input[name="criteria[' + new_id + '][criteriaName]"]').focus();
            jQuery('#help_message').empty();
            jQuery('.expand-collapse').css({ 'display' : 'inline-block' });
            jQuery('#options_window_' + new_id ).hide();

        },

        //---
        remove_criteria: function ( e ) {
            var item         = jQuery( e.target ).closest('.group');
            var criteria_id  = jQuery( e.target ).attr('data-criteria-id');
            if ( criteria_id ) {
                AMARK.add_deletion_reference( 'criteriaID', criteria_id );
            }
            jQuery( item ).slideToggle( 300, function () {
                jQuery( item ).remove();
            });
        },

        //---
        change_criteria_type: function ( e ) {
            var selected    = jQuery( e.target ).val();
            console.log(selected);
            var criteria_id = jQuery( e.target ).attr('data-criteria-id');
            if ( 'radio' === selected ) {
                jQuery('#options_window_' + criteria_id ).show();
                jQuery('#options_window_' + criteria_id + ' .make-numeric-scale').css({ 'display':'inline-block' });
            } else if ( 'checkbox' === selected ) {
                jQuery('#options_window_' + criteria_id ).show();
                jQuery('#options_window_' + criteria_id + ' .make-numeric-scale').hide();

            } else if ( 'textarea' === selected ) {
                jQuery('#options_window_' + criteria_id ).hide();
            } else if ( 'stepScale' === selected ) {
                jQuery('#options_window_' + criteria_id ).hide();
            }
        },

        //---
        pre_criteria_submit: function ( e ) {
            AMARK.renumber_fields();
        },

/*
*   OPTIONS
*/
        add_new_option: function ( e ) {
            var new_id = AMARK.get_new_id();
            var criteria_id = jQuery( e.target ).attr('data-criteria-id');
            var markup = AMARK.new_option( new_id, criteria_id );
            jQuery('#options_wrap_' + criteria_id ).append( markup );
            jQuery('input[name="criteria[' + criteria_id + '][options][' + new_id + '][optionValue]"]').focus();
            AMARK.renumber('#options_wrap_' + criteria_id + ' .option-order' );
        },

        //---
        remove_option: function ( e ) {
            var item        = jQuery( e.target ).closest('.option');
            var option_id   = jQuery( e.target ).attr('data-option-id');
            var criteria_id = jQuery( e.target ).attr('data-criteria-id');

            if ( option_id ) {
                AMARK.add_deletion_reference( 'optionID', option_id );
            }
            jQuery( item ).slideToggle( 300, function () {
                jQuery( item ).remove();
                AMARK.renumber('#options_wrap_' + criteria_id + ' .option-order' );
            });
        },

        //--
        launch_options_window: function ( e ) {
            var criteria_id = jQuery( e.target ).attr('data-criteria-id');
            var content     = jQuery('#options_wrap_' + criteria_id ).contents();
            AMARK.add_lightbox();
            AMARK.set_launch_id( criteria_id );
            AMARK.add_lightbox_content( content );
            AMARK.lightbox_sortable_on();
        },

        //---
        save_options_window: function ( e ) {
            var content, criteria_id;
            AMARK.lightbox_sortable_off();
            content     = AMARK.get_lightbox_content();
            criteria_id = AMARK.get_launch_id();
            jQuery('#options_wrap_' + criteria_id ).empty().append( content );
            AMARK.remove_lightbox();
        },

        //---
        cancel_options_window: function ( e ) {
            // Not implemented
        },

        //---
        expand_collapse_options: function ( e ) {
            var state = jQuery( e.target ).attr('data-is-open');
            if ( '1' === state ) {
                jQuery('.options-window').slideUp();
            } else {
                jQuery('.options-window').slideDown();
            }
            jQuery( e.target ).attr('data-is-open', ( '1' === state ? '0' : '1' ) );
        },

        //---
        make_numeric_scale: function ( e ) {
            var j;
            var criteria_id = jQuery( e.target ).attr('data-criteria-id');
            jQuery('#options_wrap_' + criteria_id ).empty();
            for ( j = 1; j <= 10; j += 1 ) {
                AMARK.add_new_option( e );
            }
            AMARK.renumber_fields('#options_wrap_' + criteria_id + ' .option-value' );
        },

/*
*   LIGHTBOX
*/
        add_lightbox: function () {
            var scroll_px = jQuery('html').scrollTop();
            jQuery('#' + AMARK.ui_wrap_id ).append( AMARK.lightbox_html() );
            jQuery('#scroll_px' ).val( scroll_px );
            jQuery('html').scrollTop( 0 );
        },

        //---
        add_lightbox_content: function ( content ) {
            jQuery('#lightbox_content').empty().append( content );
        },

        //---
        get_lightbox_content: function () {
            return jQuery('#lightbox_content').contents();
        },

        //---
        set_launch_id: function ( value ) {
            jQuery('#launch_id').val( value );
        },

        //---
        get_launch_id: function () {
            return jQuery('#launch_id').val();
        },

        //---
        remove_lightbox: function () {
            var scroll_px = parseInt( jQuery('#scroll_px').val(), 10 );
            jQuery('html').scrollTop( scroll_px );
            jQuery('#lightbox').remove();
        },

        //---
        lightbox_sortable_on: function () {
            jQuery('#lightbox_content').sortable({
                update: function( event, ui ) {
                    AMARK.renumber('#lightbox_content .option-order');
                }
            });
        },

        //---
        lightbox_sortable_off: function () {
            jQuery('#lightbox_content').sortable('destroy');
        },

/*
*   HTML ELEMENTS
*/
        new_group: function ( new_id ) {
            var html = '';
            html += '<div class="group">';
            html +=     '<div class="display-order"></div>';
            html +=     '<div class="group-field name">';
            html +=         '<label><strong>Group&nbsp;&nbsp;</strong></label> ';
            html +=         '<input type="text" name="groups[' + new_id + '][groupName]" value="" />';
            html +=     '</div>';
            html +=     '<div class="group-field weighting">';
            html +=         '<label><strong>Weighting &nbsp;&nbsp;</strong></label>';
            html +=         '<input type="text" name="groups[' + new_id + '][weighting]" value="" /> %';
            html +=     '</div>';
            html +=     '<div class="group-field">';
            html +=         '<input type="hidden" class="group-order-field" name="groups[' + new_id + '][groupOrder]" value="" />';
            html +=     '</div>';
            html +=     '<div class="remove-group has-click-event" data-callback="remove_group" title="Remove Group">Remove</div>';
            html +=     '<div class="clear"></div>';
            html +=     '<p><i>You can add Criteria after saving<i></p>';
            html += '</div>';
            return html;
        },

        //---
        new_criteria: function ( new_id ) {
            var html = '';
            html += '<div class="group">';
            html +=     '<div class="display-order"></div>';
            html +=     '<div class="group-field name">';
            html +=         '<label>Criteria &nbsp;&nbsp;</label>';
            html +=         '<input type="text" name="criteria[' + new_id + '][criteriaName]" value="" />';
            html +=         '<br><label>Response Type &nbsp;&nbsp;</label>';
            html +=         '<select name="criteria[' + new_id + '][criteriaType]" class="has-change-event" data-callback="change_criteria_type" data-criteria-id="' + new_id + '">';
            html +=             '<option value="" SELECTED>Please Select</option>';
            <?php
            if($useStepScale=="on")
            {
                ?>
                html +=             '<option value="stepScale">Step Scale</option>';
                <?php

            }
            else
            {
                ?>
                html +=             '<option value="radio">Radio Buttons</option>';
                <?php

            }
            ?>
            html +=             '<option value="checkbox">Check Boxes</option>';
            html +=             '<option value="textarea">Text Area</option>';
            html +=         '</select>';
            html +=     '</div>';
            html +=     '<div class="group-field">';
            html +=         '<input type="hidden" name="criteria[' + new_id + '][criteriaOrder]" value="" class="group-order-field" />';
            html +=         '<input type="hidden" name="criteria[' + new_id + '][groupID]" value="<?php echo $group_edit_id; ?>" />';
            html +=     '</div>';
            html +=     '<div class="options-window" id="options_window_' + new_id + '">';
            html +=         '<h2>Responses</h2>';
            html +=         '<div id="options_wrap_' + new_id + '" class="options-wrap"></div>';
            html +=         '<span class="button-secondary has-click-event" data-callback="add_new_option" data-criteria-id="' + new_id + '">+ Add Response Option</span>';
            html +=         '&nbsp;&nbsp;<span class="button-secondary make-numeric-scale has-click-event" data-callback="make_numeric_scale" data-criteria-id="' + new_id + '">Create 1-10</span>';
            html +=         '&nbsp;&nbsp;<span class="button-secondary has-click-event" data-callback="launch_options_window" data-criteria-id="' + new_id + '">Sort Order</span>';
            html +=     '</div>';
            html +=     '<div class="clear"></div>';
            html +=     '<div class="remove-criteria has-click-event" data-callback="remove_criteria" title="Remove Criteria">Remove</div>';
            html += '</div>';


            return html;
        },

        //---
        new_option: function ( new_id, criteria_id ) {
            var html = '';
            html += '<div class="option">';
            html +=     '<div class="option-order"></div>&nbsp;';
            html +=         '<div class="option-form">';

            html +=           '<input type="text" name="criteria[' + criteria_id + '][options][' + new_id + '][optionValue]" value="" class="option-value" />';
            html +=           '<input type="hidden" name="criteria[' + criteria_id + '][options][' + new_id + '][optionOrder]" value="" />';
            html +=           '<input type="hidden" name="criteria[' + criteria_id + '][options][' + new_id + '][criteriaID]" value="' + criteria_id + '" />';
            html +=         '</div>';
            html +=     '<div class="remove-option has-click-event" data-callback="remove_option" data-criteria-id="' + criteria_id + '" title="Remove Option">x</div>';
            html += '</div>';
            return html;
        },

        //---
        lightbox_html: function () {
            var html = '';
            html += '<div id="lightbox" class="lightbox-frame">';
            html +=     '<div id="lightbox_window" class="lightbox-window">';
            html +=         '<p>Drag and drop the options to re-order them</p><br>';
            html +=         '<input type="hidden" id="launch_id" value="" />';
            html +=         '<input type="hidden" id="scroll_px" value="" />';
            html +=         '<div id="lightbox_content"></div>';
            html +=         '<div class="lightbox-controls"> ';
            html +=             '<span class="button-primary has-click-event" data-callback="save_options_window">&nbsp;&nbsp;&nbsp; Done &nbsp;&nbsp;&nbsp;</span>';
            html +=                 '&nbsp;';
            html +=         '</div>';
            html +=     '</div>';
            html += '</div>';
            return html;
        },

/*
*   UTILS
*/
        add_deletion_reference: function ( fieldkey, id ) { // 'groupID' 'criteriaID' 'optionID'
            jQuery('#deletion_data').append('<input type="hidden" name="delete_refs[' + fieldkey + '][]" value="' + id + '" />');
        },

        //---
        renumber: function ( selector ) {
            jQuery( selector ).each( function ( i ) {
                jQuery( this ).empty().append( i+1 );
            });
        },

        //---
        renumber_fields: function ( selector ) {
            selector = selector || '.group-order-field';
            jQuery( selector ).each( function ( i ) {
                jQuery( this ).val( i+1 );
            });
        },

        //---
        get_new_id: function () {
            AMARK.new_id += 1;
            return 'n_' + AMARK.new_id;
        },

        //---
        get_last_id: function () {
            return 'n_' + AMARK.new_id;
        },

        //---
        request: function ( info, action, onSuccess, onError ) {
            var data = {
                'action':	action,
                'info':		info
            };
            jQuery.ajax({
                type: 		"POST",
                data: 		data,
                url: 		AMARK.ajax_url,
                success: function( response, status ) {
                    onSuccess( response, status );
                },
                error: function ( jqXHR, status, error ) {
                    onError( jqXHR, status, error );
                }
            });
        }
    };


    // Start up
    jQuery( document ).ready( function () {
        AMARK.init();
    });
</script>



<style>

#marking_ui {
    padding:  0 40px 0 0;
}

#marking_ui .button-primary, #marking_ui .button-secondary {
    font-weight: 600;
}

.expand-collapse {
    float:  none;
}

#marking_ui .hidden-control {
    display:    none;
}


.breadcrumbs {
    font-size: 13px;
    font-weight: 500;
    color: #bbb;
}

.breadcrumbs span{
    color: #333;
}


#groups_wrap, #groups_wrap div {
    box-sizing:  border-box;
}

#groups_wrap {
    border:     0px solid #04a;
    margin:     0 0 20px 0;
}

.group {
    position:   relative;
    border:     0px solid #b1b1b1;
    padding:    14px 10px 14px 20px;
    margin:     9px 0px;
    background: #fff;
    box-shadow: 1px 1px 3px #00000036;
    border-radius: 2px;
    cursor:     move;
}

.group label {
    vertical-align: baseline;
    cursor: move;
}

.group-field {
    float:          left;
}

.group-field.name {
    width:          47%;
}

.group-field.name input {
    width:          90%;
    font-size:      15px;
    font-weight:    600;
}

.group-field.weighting {
    width:          200px;
}

.group-field.weighting input {
    width:          50px;
    font-size:      16px;
    font-weight:    700;
}

.remove-group {
    float:          right;
    width:          70px;
    cursor:         pointer;
    color:          #b00;
    font-weight:    600;
    padding-top:    5px;
    margin:         0 0 0 15px;
}

.remove-group:hover {
    color:              #b00;
    text-decoration:    underline;
}

.add-edit {

    margin-top:10px;
}


.remove-criteria {
    width:          110px;
    cursor:         pointer;
    color:          #b00;
    font-weight:    600;
    padding-top:    0px;
    margin:         10px 0 0 0px;
}

.remove-criteria:hover {
    color:              #b00;
    text-decoration:    underline;
}

.criteria-items .group-field.name input {
    width:          80%;
    font-size:      14px;
    font-weight:    600;
}

.criteria-items .group label {
    display: inline-block;
    width: 105px;
}


.display-order {
    position:       absolute;
    top:            0;
    left:           5px;
    font-size:      10px;
    color:          #bbc8d8;
    font-weight:    700;
}

.criteria-items .display-order {

}

.group-items .display-order {

}


.options-window {
    float:      right;
    width:      50%;
    border:     0px solid #e8e8e8;
    margin:     0px 10px 0 0;
    padding:    6px 10px 10px 10px;
    cursor:     default;
}

.options-window h2 {
    margin: 0 0 15px 0;
}

.options-wrap {
    padding:    0 0 0px 0;
}

.options-wrap .option{
   display:flex;
   flex-wrap: wrap;
   justify-content: center;

}

.options-wrap .option .option-order
{
   width:20px;
}

.options-wrap .option .option-form
{
   flex-grow:1;
}


.option-order {
    color: #aaa;
    font-size: 11px;
    padding: 0 5px;
}

.option {
    width:      100%;
    padding:    0px;
    margin:     0 0 3px 0;
}

.option:last-child {
    margin:     0 0 15px 0;
}

.option input {
    width:      100%;
    background: #fff;
}

.remove-option {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    text-align: center;
    font-size: 15px;
    line-height: 17px;
    color: #fff;
    background: #d29292; /* #003e74 */
    font-weight: 600;
    margin: 4px 15px 0 0;
    cursor: pointer;
    margin:0px 10px;
}

.remove-option:hover {
    background: #8f0000;
}


#lightbox_content .remove-option {
    display:      none;
}

#lightbox_content .option {
    width:      80%;
    padding:    4px 10px 4px 6px;
    margin:     3px 0 3px 0;
    background: #fff;
    border:     1px solid #c7c7c7;
    cursor:     move;
}
#lightbox_content .option:last-child {
    margin:     3px 0 20px 0;
}

#lightbox_content .option input {
    width:      75%;
}

#lightbox_content .option-order {
    padding: 0 10px;
}

.lightbox-frame {
    position:   absolute;
    top:        0;
    right:      0;
    bottom:     -1500px;
    left:       -50px;
    background: rgba(0,0,0,0.8);
    z-index: 99;
}

.lightbox-window {
    position:   relative;
    width:      80%;
    max-width:  700px;
    margin:     150px auto 0 auto;
    background: #f3f3f3;
    min-height: 112px;
    padding:    20px 30px;
}

.lightbox-window p {
    margin:      0 0 5px 0;
}

.lightbox-controls {
    padding:    10px 0;
}


.clear {
    clear:      both;
    width:      100%;
    height:     0;
}
</style>

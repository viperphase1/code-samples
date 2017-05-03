<?php

$field = $node->field_owner;
$this_tid = $field ? $field[LANGUAGE_NONE][0]["tid"] : "";

print "<div class='node node-type-department'>";

hide($content['body']);
hide($content['field_resources_opportunity']);
hide($content['field_financial_assistance']);

print "<div class='before-children'>";

/*chairs and coordinators*/
if($this_tid) {
    print "<div class='administration-section'>";

    $query = new EntityFieldQuery();
    $result = $query
    ->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'faculty')
    ->fieldCondition('field_owner','tid',$this_tid,'=')
    ->fieldCondition('field_administrative_role', 'tid', [227,228,233], 'IN')
    ->execute();
    echo "<!--RESULT: " . print_r($result,true) . "-->";

    print "</div>";
}


print "<div class='catalog-page-title'>" . $node->title . "</div>";
print render($content);
print "</div>";

if($this_tid) { 

    print "<div class='catalog-lower-level'>";

    $query = new EntityFieldQuery();

    $result = $query
    ->entityCondition('entity_type', 'node')
    ->propertyCondition('type','program')
    ->propertyCondition('status', NODE_PUBLISHED)
    ->fieldCondition('field_owner', 'tid', $this_tid, '=')
    ->execute();

    if(!empty($result['node'])) {
        $programs = [];
        print "<div class='children-type'>Programs:</div>";
        $program_nid_array = array_keys($result['node']);
        $sql_inject = implode(",",$program_nid_array);
        $get_ent_ids = db_query("SELECT entity_id, field_program_type_tid FROM field_data_field_program_type WHERE entity_id IN ({$sql_inject})");
        $ent_ids = $get_ent_ids->fetchAll();
        $map_entid_to_tid = array();
        foreach($ent_ids as $obj) {
            $map_entid_to_tid[$obj->entity_id] = $obj->field_program_type_tid;
        }
        foreach($program_nid_array as $nid) {
            $path = "node/" . $nid;
            //$program_type = $program->field_program_type;
            $url = drupal_get_path_alias($path);

            $program_type = $map_entid_to_tid[$nid] ? "(" . taxonomy_term_load($map_entid_to_tid[$nid])->name . ")" : "";

            $get_title = db_query("SELECT title FROM {node} WHERE nid=:nid",array(":nid" => $nid));
            $title = $get_title->fetchAssoc()['title'];

            $this_program = array();
            $this_program["url"] = $url;
            $this_program["link-text"] = $title . " " . $program_type;
            array_push($programs,$this_program);
        }
        usort($programs,function($a,$b) {
            return strcmp($a["link-text"],$b["link-text"]);
        });
        foreach($programs as $program) {
            print "<a class='program-link' href='/" . $program['url'] . "'>{$program['link-text']}</a><br>";
        }
    }

    $field = $node->field_hide_faculty_button;
    $hide_faculty = isset($field[LANGUAGE_NONE]) ? $field[LANGUAGE_NONE][0]["value"] : false;

    $query = new EntityFieldQuery();
    $num_faculty = $query
    ->entityCondition('entity_type', 'node')
    ->propertyCondition('type','personnel')
    ->propertyCondition('status', NODE_PUBLISHED)
    ->fieldCondition('field_owner', 'tid', $this_tid, '=')
    ->range(0,1)
    ->count()
    ->execute();

    $query = new EntityFieldQuery();
    $num_courses = $query
    ->entityCondition('entity_type', 'node')
    ->propertyCondition('type','course')
    ->propertyCondition('status', NODE_PUBLISHED)
    ->fieldCondition('field_owner', 'tid', $this_tid, '=')
    ->range(0,1)
    ->count()
    ->execute();

    if($num_faculty > 0 || $num_courses > 0) {
        print "<div class='link-buttons'>";
        if($num_faculty > 0 && !$hide_faculty) {
            print "<div class='subject-areas button-class'>";
            print "<a href='/filter-faculty/" . $this_tid . "'><i class='fa fa-user' aria-hidden='true'></i> Faculty</a>";
            print "</div>";
        }
        if($num_courses > 0) {
            print "<div class='subject-areas button-class'>";
            print "<a href='/filter-courses/" . $this_tid . "'><i class='fa fa-book' aria-hidden='true'></i> Courses</a>";
            print "</div>";
        }
        print "</div>";
    }

    print "</div>";
}

print "<div class='after-children'>";
print render($content['body']);
print render($content['field_resources_opportunity']);
print render($content['field_financial_assistance']);

print "</div>";
print "</div>";
?>

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
	 * Version info
	 *
	 * @package	open_api
	 * @author	Matthias Hartmann
	 * @var stdClass $plugin
	 */

	require_once(__DIR__ . '/../../config.php');
	require_once($CFG->dirroot . '/course/lib.php');

	try {
		$courseID = 0;
		
		if(array_key_exists("id", $_GET)) {
			$courseID = intval(htmlentities(strip_tags($_GET["id"])));
		}
		
		$courses = json_decode(json_encode(get_courses()), true);
		$jsonArr = [];

		foreach ($courses as $c) {

			if (boolval($c["visible"]) == false) {
				continue;
			}
			
			if($courseID > 0 && $courseID != intval($c["id"])) {
				continue;
			}
			
			// --- teachers ---
			$role = $DB->get_record('role', array('shortname' => 'editingteacher'));
			$context = get_context_instance(CONTEXT_COURSE, $c["id"]);
			$teachers = get_role_users($role->id, $context);
			$teachers = json_decode(json_encode($teachers), true);

			$jTeachers = [];
			
			foreach($teachers as $t) {
				$jT = [];
				
				$jT["id"] = $t["id"];
				$jT["name"] = $t["firstname"] . " " . $t["lastname"];
				
				$jTeachers[] = $jT;
			}
			

			// --- course details and assembly ---
			$jCourse = [];

			$jCourse["id"]         = $c["id"];
			$jCourse["courses"]    = "courses";
			$jCourse["attributes"] = [
				"name"              => $c["fullname"],
				"abstract"          => $c["summary"],
				"availableLanguage" => $c["lang"],
				"uniformResourceLocator" => $CFG->wwwroot . "/course/view.php?id=" . $c["id"],
				"instructors"       => $jTeachers,
			];

			$jsonArr[] = $jCourse;
		}

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(["data" => $jsonArr]);
	} catch (Throwable $e) {
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(
			[
				"some_error" =>
					[
						"msg" => $e->getMessage(),
						"file" => $e->getFile(),
						"line" => $e->getLine()
					]
			]
		);
	}

?>
<?php
// SQL queries for each report

// Query 1: Average GPA of each student
$query1 = "SELECT s.full_name, p.program_name, 
           AVG(g.grade_point) AS average_gpa
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN Grades g ON e.enroll_id = g.enroll_id
           GROUP BY s.student_id, s.full_name, p.program_name
           ORDER BY average_gpa DESC";

// Query 2: List of students and their enrolled courses
$query2 = "SELECT s.full_name, c.course_name, co.academic_year, co.semester
           FROM Students s
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN CourseOfferings co ON e.offer_id = co.offer_id
           JOIN Courses c ON co.course_id = c.course_id
           ORDER BY s.full_name, co.academic_year, co.semester";

// Query 3: Top 3 students with highest GPA in level 4
$query3 = "SELECT s.full_name, p.program_name, 
           AVG(g.grade_point) AS average_gpa,
           COUNT(DISTINCT e.offer_id) AS courses_completed
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN Grades g ON e.enroll_id = g.enroll_id
           WHERE s.level = 4
           GROUP BY s.student_id, s.full_name, p.program_name
           ORDER BY average_gpa DESC
           LIMIT 3";

// Query 4: Courses offered in Semester 2 of 2023/2024
$query4 = "SELECT c.course_name, i.full_name AS instructor, 
           COUNT(e.student_id) AS enrolled_students
           FROM CourseOfferings co
           JOIN Courses c ON co.course_id = c.course_id
           JOIN Instructors i ON co.instructor_id = i.instructor_id
           LEFT JOIN Enrollments e ON co.offer_id = e.offer_id
           WHERE co.academic_year = '2023/2024' AND co.semester = 'Semester 2'
           GROUP BY co.offer_id, c.course_name, i.full_name
           ORDER BY c.course_name";

// Query 5: Students who scored an 'A' in any course
$query5 = "SELECT s.full_name, c.course_name, co.academic_year, g.letter_grade
           FROM Students s
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN CourseOfferings co ON e.offer_id = co.offer_id
           JOIN Courses c ON co.course_id = c.course_id
           JOIN Grades g ON e.enroll_id = g.enroll_id
           WHERE g.letter_grade = 'A'
           ORDER BY s.full_name, co.academic_year";

// Query 6: Number of courses offered by each department
$query6 = "SELECT d.dept_name, COUNT(c.course_id) AS total_courses
           FROM Departments d
           LEFT JOIN Courses c ON d.dept_id = c.dept_id
           GROUP BY d.dept_id, d.dept_name
           ORDER BY total_courses DESC";

// Query 7: Students who completed all core CS courses (CS101, CS201, CS301)
$query7 = "SELECT s.full_name, p.program_name
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           WHERE s.student_id IN (
               SELECT e.student_id 
               FROM Enrollments e
               JOIN CourseOfferings co ON e.offer_id = co.offer_id
               JOIN Courses c ON co.course_id = c.course_id
               WHERE c.course_id IN ('CS101', 'CS201', 'CS301')
               GROUP BY e.student_id
               HAVING COUNT(DISTINCT c.course_id) = 3
           )
           ORDER BY s.full_name";

// Query 8: Each course with total enrolled students, instructor name, and department
$query8 = "SELECT c.course_name, 
           COUNT(e.student_id) AS enrolled_students,
           i.full_name AS instructor_name,
           d.dept_name
           FROM Courses c
           JOIN CourseOfferings co ON c.course_id = co.course_id
           JOIN Instructors i ON co.instructor_id = i.instructor_id
           JOIN Departments d ON c.dept_id = d.dept_id
           LEFT JOIN Enrollments e ON co.offer_id = e.offer_id
           GROUP BY c.course_id, c.course_name, i.full_name, d.dept_name
           ORDER BY c.course_name";

// Query 9: All level 4 students
$query9 = "SELECT s.full_name, s.gender, p.program_name, s.level
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           WHERE s.level = 4
           ORDER BY s.full_name";

// Query 10: Average grade per department
$query10 = "SELECT d.dept_name, 
            COUNT(DISTINCT c.course_id) AS number_of_courses,
            AVG(g.grade_point) AS average_grade
            FROM Departments d
            JOIN Courses c ON d.dept_id = c.dept_id
            JOIN CourseOfferings co ON c.course_id = co.course_id
            JOIN Enrollments e ON co.offer_id = e.offer_id
            JOIN Grades g ON e.enroll_id = g.enroll_id
            GROUP BY d.dept_id, d.dept_name
            ORDER BY average_grade DESC";

// Array of all queries with titles for easier processing
$all_queries = [
    [
        'id' => 1,
        'title' => 'Average GPA of Each Student',
        'description' => 'Report showing the average GPA (grade_point) of each student, including full name, program name, and GPA.',
        'query' => $query1,
        'chart_type' => 'bar',
        'chart_x' => 'full_name',
        'chart_y' => 'average_gpa',
        'chart_title' => 'Student GPAs'
    ],
    [
        'id' => 2,
        'title' => 'Students and Their Enrolled Courses',
        'description' => 'List of students and the courses they are enrolled in, including student name, course name, academic year, and semester.',
        'query' => $query2,
        'chart_type' => 'none'
    ],
    [
        'id' => 3,
        'title' => 'Top 3 Students with Highest GPA in Level 4',
        'description' => 'The top 3 students with the highest GPA in level 4, showing full name, program, GPA, and number of courses completed.',
        'query' => $query3,
        'chart_type' => 'bar',
        'chart_x' => 'full_name',
        'chart_y' => 'average_gpa',
        'chart_title' => 'Top 3 Level 4 Students by GPA'
    ],
    [
        'id' => 4,
        'title' => 'Courses Offered in Semester 2 of 2023/2024',
        'description' => 'All courses offered in Semester 2 of the 2023/2024 academic year, showing course name, instructor, and number of students enrolled.',
        'query' => $query4,
        'chart_type' => 'bar',
        'chart_x' => 'course_name',
        'chart_y' => 'enrolled_students',
        'chart_title' => 'Enrollment by Course (Semester 2, 2023/2024)'
    ],
    [
        'id' => 5,
        'title' => 'Students Who Scored an A in Any Course',
        'description' => 'All students who scored an \'A\' in any course, showing full name, course name, academic year, and letter grade.',
        'query' => $query5,
        'chart_type' => 'none'
    ],
    [
        'id' => 6,
        'title' => 'Number of Courses Offered by Each Department',
        'description' => 'The number of courses offered by each department, including department name and total courses.',
        'query' => $query6,
        'chart_type' => 'pie',
        'chart_labels' => 'dept_name',
        'chart_data' => 'total_courses',
        'chart_title' => 'Course Distribution by Department'
    ],
    [
        'id' => 7,
        'title' => 'Students Who Completed All Core CS Courses',
        'description' => 'Students who have completed all of the following core CS courses: CS101, CS201, and CS301, showing full name and program.',
        'query' => $query7,
        'chart_type' => 'none'
    ],
    [
        'id' => 8,
        'title' => 'Each Course with Total Enrolled Students',
        'description' => 'Each course with the total number of enrolled students, the instructor\'s name, and the department.',
        'query' => $query8,
        'chart_type' => 'bar',
        'chart_x' => 'course_name',
        'chart_y' => 'enrolled_students',
        'chart_title' => 'Enrollment by Course'
    ],
    [
        'id' => 9,
        'title' => 'All Level 4 Students',
        'description' => 'All level 4 students, showing their full name, gender, program name, and level.',
        'query' => $query9,
        'chart_type' => 'none'
    ],
    [
        'id' => 10,
        'title' => 'Average Grade Per Department',
        'description' => 'The average grade per department across all courses, showing department name, number of courses, and average grade_point.',
        'query' => $query10,
        'chart_type' => 'bar',
        'chart_x' => 'dept_name',
        'chart_y' => 'average_grade',
        'chart_title' => 'Average Grade by Department'
    ]
];
?>

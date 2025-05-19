SELECT c.course_name, i.full_name AS instructor, 
           COUNT(e.student_id) AS enrolled_students
           FROM CourseOfferings co
           JOIN Courses c ON co.course_id = c.course_id
           JOIN Instructors i ON co.instructor_id = i.instructor_id
           LEFT JOIN Enrollments e ON co.offer_id = e.offer_id
           WHERE co.academic_year = '2023/2024' AND co.semester = 'Semester 2'
           GROUP BY co.offer_id, c.course_name, i.full_name
           ORDER BY c.course_name
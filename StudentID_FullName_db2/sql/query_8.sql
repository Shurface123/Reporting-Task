SELECT c.course_name, 
           COUNT(e.student_id) AS enrolled_students,
           i.full_name AS instructor_name,
           d.dept_name
           FROM Courses c
           JOIN CourseOfferings co ON c.course_id = co.course_id
           JOIN Instructors i ON co.instructor_id = i.instructor_id
           JOIN Departments d ON c.dept_id = d.dept_id
           LEFT JOIN Enrollments e ON co.offer_id = e.offer_id
           GROUP BY c.course_id, c.course_name, i.full_name, d.dept_name
           ORDER BY c.course_name
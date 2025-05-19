SELECT d.dept_name, 
            COUNT(DISTINCT c.course_id) AS number_of_courses,
            AVG(g.grade_point) AS average_grade
            FROM Departments d
            JOIN Courses c ON d.dept_id = c.dept_id
            JOIN CourseOfferings co ON c.course_id = co.course_id
            JOIN Enrollments e ON co.offer_id = e.offer_id
            JOIN Grades g ON e.enroll_id = g.enroll_id
            GROUP BY d.dept_id, d.dept_name
            ORDER BY average_grade DESC
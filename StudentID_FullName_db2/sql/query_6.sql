SELECT d.dept_name, COUNT(c.course_id) AS total_courses
           FROM Departments d
           LEFT JOIN Courses c ON d.dept_id = c.dept_id
           GROUP BY d.dept_id, d.dept_name
           ORDER BY total_courses DESC
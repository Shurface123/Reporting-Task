SELECT s.full_name, p.program_name, 
           AVG(g.grade_point) AS average_gpa,
           COUNT(DISTINCT e.offer_id) AS courses_completed
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN Grades g ON e.enroll_id = g.enroll_id
           WHERE s.level = 4
           GROUP BY s.student_id, s.full_name, p.program_name
           ORDER BY average_gpa DESC
           LIMIT 3
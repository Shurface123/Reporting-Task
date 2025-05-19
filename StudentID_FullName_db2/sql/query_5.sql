SELECT s.full_name, c.course_name, co.academic_year, g.letter_grade
           FROM Students s
           JOIN Enrollments e ON s.student_id = e.student_id
           JOIN CourseOfferings co ON e.offer_id = co.offer_id
           JOIN Courses c ON co.course_id = c.course_id
           JOIN Grades g ON e.enroll_id = g.enroll_id
           WHERE g.letter_grade = 'A'
           ORDER BY s.full_name, co.academic_year
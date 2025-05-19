SELECT s.full_name, p.program_name
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
           ORDER BY s.full_name
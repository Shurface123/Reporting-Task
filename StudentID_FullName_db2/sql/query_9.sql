SELECT s.full_name, s.gender, p.program_name, s.level
           FROM Students s
           JOIN Programs p ON s.program_id = p.program_id
           WHERE s.level = 4
           ORDER BY s.full_name
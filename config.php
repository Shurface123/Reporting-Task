<?php
// Database connection configuration
$host = 'localhost';
$username = 'root';  // Default WAMP username
$password = 'Confrontation@433';      // Default WAMP password (empty)
$database = 'student_db';

// Create database connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Function to initialize the database with tables and data
function initializeDatabase($conn) {
    // Drop existing tables for reset
    $tables = ["Enrollments", "CourseOfferings", "Grades", "Courses", "Instructors", 
               "Students", "Departments", "Programs"];
    
    foreach ($tables as $table) {
        $conn->query("DROP TABLE IF EXISTS $table");
    }

    // Create Departments table
    $sql = "CREATE TABLE Departments (
        dept_id INT PRIMARY KEY,
        dept_name VARCHAR(100)
    )";
    $conn->query($sql);

    // Insert Departments data
    $sql = "INSERT INTO Departments VALUES 
        (1, 'Computer Science'),
        (2, 'Electrical Engineering'),
        (3, 'Mechanical Engineering'),
        (4, 'Information Technology')";
    $conn->query($sql);

    // Create Programs table
    $sql = "CREATE TABLE Programs (
        program_id INT PRIMARY KEY,
        program_name VARCHAR(100),
        dept_id INT,
        duration_years INT,
        FOREIGN KEY (dept_id) REFERENCES Departments(dept_id)
    )";
    $conn->query($sql);

    // Insert Programs data
    $sql = "INSERT INTO Programs VALUES 
        (101, 'BSc Computer Science', 1, 4),
        (102, 'BSc Electrical Engineering', 2, 4),
        (103, 'BSc Mechanical Engineering', 3, 4),
        (104, 'BSc IT', 4, 4)";
    $conn->query($sql);

    // Create Students table
    $sql = "CREATE TABLE Students (
        student_id INT PRIMARY KEY,
        full_name VARCHAR(100),
        gender CHAR(1),
        dob DATE,
        program_id INT,
        level INT,
        FOREIGN KEY (program_id) REFERENCES Programs(program_id)
    )";
    $conn->query($sql);

    // Insert Students data
    $sql = "INSERT INTO Students VALUES 
        (2001, 'Ama Boateng', 'F', '2002-01-20', 101, 3),
        (2002, 'Kwame Mensah', 'M', '2001-03-10', 102, 4),
        (2003, 'Efua Sarpong', 'F', '2003-08-10', 104, 2),
        (2004, 'Kojo Asante', 'M', '2002-12-15', 104, 3),
        (2005, 'Akua Owusu', 'F', '2001-06-11', 101, 4),
        (2006, 'Yaw Addo', 'M', '2002-07-30', 103, 3),
        (2007, 'Esi Adjei', 'F', '2003-09-25', 102, 2)";
    $conn->query($sql);

    // Create Instructors table
    $sql = "CREATE TABLE Instructors (
        instructor_id INT PRIMARY KEY,
        full_name VARCHAR(100),
        dept_id INT,
        `rank` VARCHAR(50),
        FOREIGN KEY (dept_id) REFERENCES Departments(dept_id)
    )";
    $conn->query($sql);

    // Insert Instructors data
    $sql = "INSERT INTO Instructors (instructor_id, full_name, dept_id, `rank`) VALUES 
        (301, 'Dr. Nana Quaye', 1, 'Senior Lecturer'),
        (302, 'Dr. John Tetteh', 2, 'Lecturer'),
        (303, 'Prof. Linda Koranteng', 1, 'Professor'),
        (304, 'Dr. James Owusu', 4, 'Senior Lecturer'),
        (305, 'Dr. Sarah Mensah', 3, 'Lecturer')";
    $conn->query($sql);

    // Create Courses table
    $sql = "CREATE TABLE Courses (
        course_id VARCHAR(10) PRIMARY KEY,
        course_name VARCHAR(100),
        credit_hours INT,
        dept_id INT,
        FOREIGN KEY (dept_id) REFERENCES Departments(dept_id)
    )";
    $conn->query($sql);

    // Insert Courses data
    $sql = "INSERT INTO Courses VALUES 
        ('CS101', 'Programming Fundamentals', 3, 1),
        ('CS201', 'Data Structures', 3, 1),
        ('CS301', 'Database Systems', 3, 1),
        ('EE101', 'Circuit Theory', 3, 2),
        ('ME201', 'Thermodynamics', 3, 3),
        ('IT301', 'Computer Networks', 3, 4),
        ('CS401', 'Machine Learning', 3, 1),
        ('CS305', 'Software Engineering', 3, 1)";
    $conn->query($sql);

    // Create CourseOfferings table
    $sql = "CREATE TABLE CourseOfferings (
        offer_id INT PRIMARY KEY,
        course_id VARCHAR(10),
        instructor_id INT,
        academic_year VARCHAR(9),
        semester VARCHAR(10),
        FOREIGN KEY (course_id) REFERENCES Courses(course_id),
        FOREIGN KEY (instructor_id) REFERENCES Instructors(instructor_id)
    )";
    $conn->query($sql);

    // Insert CourseOfferings data
    $sql = "INSERT INTO CourseOfferings VALUES 
        (1, 'CS101', 301, '2023/2024', 'Semester 1'),
        (2, 'CS201', 303, '2023/2024', 'Semester 2'),
        (3, 'CS301', 303, '2023/2024', 'Semester 2'),
        (4, 'EE101', 302, '2023/2024', 'Semester 1'),
        (5, 'ME201', 305, '2023/2024', 'Semester 2'),
        (6, 'IT301', 304, '2023/2024', 'Semester 2'),
        (7, 'CS401', 303, '2023/2024', 'Semester 2'),
        (8, 'CS305', 301, '2023/2024', 'Semester 1')";
    $conn->query($sql);

    // Create Enrollments table
    $sql = "CREATE TABLE Enrollments (
        enroll_id INT PRIMARY KEY,
        student_id INT,
        offer_id INT,
        FOREIGN KEY (student_id) REFERENCES Students(student_id),
        FOREIGN KEY (offer_id) REFERENCES CourseOfferings(offer_id)
    )";
    $conn->query($sql);

    // Insert Enrollments data
    $sql = "INSERT INTO Enrollments VALUES 
        (1, 2001, 1),
        (2, 2001, 2),
        (3, 2001, 3),
        (4, 2002, 4),
        (5, 2003, 1),
        (6, 2003, 6),
        (7, 2004, 6),
        (8, 2005, 3),
        (9, 2005, 7),
        (10, 2006, 5),
        (11, 2007, 4),
        (12, 2001, 8)";
    $conn->query($sql);

    // Create Grades table
    $sql = "CREATE TABLE Grades (
        enroll_id INT PRIMARY KEY,
        letter_grade CHAR(2),
        grade_point DECIMAL(3,2),
        FOREIGN KEY (enroll_id) REFERENCES Enrollments(enroll_id)
    )";
    $conn->query($sql);

    // Insert Grades data
    $sql = "INSERT INTO Grades VALUES 
        (1, 'A', 4.0),
        (2, 'B+', 3.5),
        (3, 'A', 4.0),
        (4, 'B', 3.0),
        (5, 'A-', 3.7),
        (6, 'B+', 3.5),
        (7, 'A', 4.0),
        (8, 'B+', 3.5),
        (9, 'A-', 3.7),
        (10, 'C+', 2.5),
        (11, 'A', 4.0),
        (12, 'A', 4.0)";
    $conn->query($sql);

    return true;
}
?>

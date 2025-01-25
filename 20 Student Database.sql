CREATE DATABASE StudentDatabase;

-- Creating a table 
CREATE TABLE students (
    name VARCHAR(50),
    age INT,
    gender VARCHAR(10),
    cgpa DECIMAL(3, 2),
    school VARCHAR(50)
);

-- 20 basic student information
INSERT INTO students (name, age, gender, cgpa, school)
VALUES
('John Smith', 22, 'Male', 3.45, 'School of Health Science'),
('Ali Akbar', 19, 'Male', 3.90, 'School of ICT'),
('Brotherhood of N.O.D', 25, 'Male', 2.75, 'School of Engineering'),
('Scarlett Johansson', 20, 'Female', 3.15, 'School of Health Science'),
('Omar Adnan', 18, 'Male', 3.70, 'School of ICT'),
('Charles Olivera', 21, 'Male', 2.85, 'School of Engineering'),
('Dustin Poirier', 23, 'Male', 3.25, 'School of Health Science'),
('Justin Gaethje', 24, 'Male', 3.50, 'School of ICT'),
('Conor McGregor', 17, 'Male', 3.80, 'School of Engineering'),
('Islam Makhachev', 26, 'Male', 3.65, 'School of Health Science'),
('Khabib Nurmagomedov', 28, 'Male', 3.00, 'School of ICT'),
('Muhammad Ali', 16, 'Male', 2.95, 'School of Engineering'),
('Siti Syafiqah', 27, 'Female', 3.40, 'School of Health Science'),
('Mia Harris', 22, 'Female', 3.85, 'School of ICT'),
('Ethan Martin', 18, 'Male', 2.70, 'School of Engineering'),
('Amelia Lee', 21, 'Female', 3.55, 'School of Health Science'),
('Noah Walker', 19, 'Male', 3.20, 'School of ICT'),
('Charlotte Hall', 20, 'Female', 3.30, 'School of Engineering'),
('Mike Tyson', 29, 'Male', 3.10, 'School of Health Science'),
('Harper King', 24, 'Female', 3.95, 'School of ICT');


-- Creating enrollments table
CREATE TABLE enrollments (
    intake VARCHAR(2),
    student_id VARCHAR(9),
    course_id VARCHAR(4),
    grade VARCHAR(12)
);

-- Student enrollment table
INSERT INTO enrollments (intake, student_id, course_id, grade)
VALUES
    ('11', '11FTT1001', 'DDAT', 'Distinction'),
    ('11', '11FTT1502', 'DCNG', 'Merit'),
    ('12', '12FTT1203', 'DNUS', 'Pass'),
    ('12', '12FTT1404', 'DPAH', 'Fail'),
    ('13', '13FTT1705', 'DATE', 'Distinction'),
    ('13', '13FTT1806', 'DEEE', 'Merit'),
    ('14', '14FTT1907', 'DDAT', 'Pass'),
    ('14', '14FTT1108', 'DCNG', 'Fail'),
    ('11', '11FTT1023', 'DNUS', 'Pass'),
    ('12', '12FTT1309', 'DPAH', 'Merit'),
    ('13', '13FTT1500', 'DATE', 'Distinction'),
    ('14', '14FTT1601', 'DEEE', 'Fail'),
    ('11', '11FTT1202', 'DDAT', 'Merit'),
    ('12', '12FTT1103', 'DCNG', 'Distinction'),
    ('13', '13FTT1404', 'DNUS', 'Fail'),
    ('14', '14FTT1705', 'DPAH', 'Pass'),
    ('11', '11FTT1506', 'DATE', 'Distinction'),
    ('12', '12FTT1807', 'DEEE', 'Merit'),
    ('13', '13FTT1908', 'DDAT', 'Fail'),
    ('14', '14FTT1009', 'DCNG', 'Pass');

-- Creating Student contacts table
CREATE TABLE student_contacts (
    student_id VARCHAR(9),
    phone_number VARCHAR(15), 
    email VARCHAR(50) 
);

-- Student contacts table
INSERT INTO student_contacts (student_id, phone_number, email)
VALUES
    ('11FTT1001', '8231001', 'johnsmith@gmail.com'),
    ('11FTT1502', '8231502', 'aliakbar@gmail.com'),
    ('12FTT1203', '8231203', 'brotherhoodnod@gmail.com'),
    ('12FTT1404', '8231404', 'scarlettjohansson@gmail.com'),
    ('13FTT1705', '8231705', 'omaradnan@gmail.com'),
    ('13FTT1806', '8231806', 'charlesolivera@gmail.com'),
    ('14FTT1907', '8231907', 'dustinpoirier@gmail.com'),
    ('14FTT1108', '8231108', 'justingaethje@gmail.com'),
    ('11FTT1023', '8231023', 'conormcgregor@gmail.com'),
    ('12FTT1309', '8231309', 'islammakhachev@gmail.com'),
    ('13FTT1500', '8231500', 'khabibnurmagomedov@gmail.com'),
    ('14FTT1601', '8231601', 'muhammadali@gmail.com'),
    ('11FTT1202', '8231202', 'sitisyafiqah@gmail.com'),
    ('12FTT1103', '8231103', 'miaharris@gmail.com'),
    ('13FTT1404', '8231404', 'ethanmartin@gmail.com'),
    ('14FTT1705', '8231705', 'amelialee@gmail.com'),
    ('11FTT1506', '8231506', 'noahwalker@gmail.com'),
    ('12FTT1807', '8231807', 'charlottehall@gmail.com'),
    ('13FTT1908', '8231908', 'miketyson@gmail.com'),
    ('14FTT1009', '8231009', 'harperking@gmail.com');

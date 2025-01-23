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


-- Create the enrollments table
CREATE TABLE enrollments (
    intake VARCHAR(4), 
    student_id VARCHAR(8), 
    course_id VARCHAR(4), 
    grade VARCHAR(11), 
    PRIMARY KEY (intake, student_id, course_id)
);


-- Data for enrollments table
INSERT INTO enrollments (intake, student_id, course_id, grade)
VALUES
('11', '11FTT1050', 'DDAT', 'Distinction'),
('11', '11FTT1152', 'DCNG', 'Merit'),
('11', '11FTT1345', 'DNUS', 'Pass'),
('11', '11FTT1423', 'DPAH', 'Fail'),
('11', '11FTT1999', 'DEEE', 'Distinction'),
('12', '12FTT1020', 'DDAT', 'Merit'),
('12', '12FTT1125', 'DCNG', 'Pass'),
('12', '12FTT1234', 'DNUS', 'Fail'),
('12', '12FTT1456', 'DPAH', 'Distinction'),
('12', '12FTT1990', 'DATE', 'Merit'),
('13', '13FTT1015', 'DDAT', 'Pass'),
('13', '13FTT1254', 'DCNG', 'Distinction'),
('13', '13FTT1378', 'DNUS', 'Merit'),
('13', '13FTT1420', 'DPAH', 'Fail'),
('13', '13FTT1988', 'DEEE', 'Distinction'),
('14', '14FTT1045', 'DDAT', 'Fail'),
('14', '14FTT1178', 'DCNG', 'Merit'),
('14', '14FTT1289', 'DNUS', 'Pass'),
('14', '14FTT1350', 'DPAH', 'Distinction'),
('14', '14FTT1900', 'DATE', 'Merit');
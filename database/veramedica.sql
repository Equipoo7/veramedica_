DROP DATABASE IF EXISTS veramedica;
CREATE DATABASE veramedica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE veramedica;

CREATE TABLE roles(
 id_rol INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(30) NOT NULL UNIQUE
);
INSERT INTO roles(nombre) VALUES ('cliente'),('mostrador'),('doctor');

CREATE TABLE usuarios(
 id_usuario INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 correo VARCHAR(150) NOT NULL UNIQUE,
 telefono VARCHAR(20),
 password_hash VARCHAR(255) NOT NULL,
 id_rol INT NOT NULL,
 estado TINYINT DEFAULT 1,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

CREATE TABLE doctores(
 id_doctor INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL UNIQUE,
 especialidad VARCHAR(100) NOT NULL,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE personal_mostrador(
 id_personal INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NULL UNIQUE,
 nombre VARCHAR(120) NOT NULL,
 turno VARCHAR(80) NOT NULL,
 notas TEXT,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE horarios_personal(
 id_horario_personal INT AUTO_INCREMENT PRIMARY KEY,
 id_personal INT NOT NULL,
 dia_semana ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fin TIME NOT NULL,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_personal) REFERENCES personal_mostrador(id_personal)
);


CREATE TABLE servicios(
 id_servicio INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 descripcion TEXT,
 precio DECIMAL(10,2) DEFAULT 0,
 id_doctor INT NULL,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor)
);

CREATE TABLE categorias(
 id_categoria INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL
);

CREATE TABLE productos(
 id_producto INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 descripcion VARCHAR(255),
 precio DECIMAL(10,2) NOT NULL,
 stock INT DEFAULT 0,
 imagen VARCHAR(255),
 id_categoria INT,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_categoria) REFERENCES categorias(id_categoria)
);

CREATE TABLE promociones(
 id_promocion INT AUTO_INCREMENT PRIMARY KEY,
 titulo VARCHAR(120) NOT NULL,
 descripcion TEXT,
 descuento INT DEFAULT 0,
 fecha_inicio DATE,
 fecha_fin DATE,
 activo TINYINT DEFAULT 1
);

CREATE TABLE horarios_doctores(
 id_horario INT AUTO_INCREMENT PRIMARY KEY,
 id_doctor INT NOT NULL,
 dia_semana ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
 hora_inicio TIME NOT NULL,
 hora_fin TIME NOT NULL,
 activo TINYINT DEFAULT 1,
 FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor)
);

CREATE TABLE citas(
 id_cita INT AUTO_INCREMENT PRIMARY KEY,
 id_cliente_usuario INT NULL,
 id_doctor INT NOT NULL,
 id_servicio INT NOT NULL,
 nombre_paciente VARCHAR(120) NOT NULL,
 apellido_paterno VARCHAR(100),
 apellido_materno VARCHAR(100),
 fecha_nacimiento DATE NULL,
 sexo ENUM('Mujer','Hombre','Otro') DEFAULT 'Otro',
 correo VARCHAR(150),
 whatsapp VARCHAR(20) NOT NULL,
 fecha DATE NOT NULL,
 hora TIME NOT NULL,
 estado ENUM('Pendiente','Confirmada','Cancelada','Atendida','No asistió') DEFAULT 'Pendiente',
 observaciones TEXT,
 diagnostico TEXT NULL,
 tratamiento TEXT NULL,
 observaciones_medicas TEXT NULL,
 fecha_atencion DATETIME NULL,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(id_cliente_usuario) REFERENCES usuarios(id_usuario),
 FOREIGN KEY(id_doctor) REFERENCES doctores(id_doctor),
 FOREIGN KEY(id_servicio) REFERENCES servicios(id_servicio),
 UNIQUE KEY cita_unica (id_doctor, fecha, hora, estado)
);

CREATE TABLE recuperacion_password(
 id_recuperacion INT AUTO_INCREMENT PRIMARY KEY,
 id_usuario INT NOT NULL,
 token VARCHAR(100) NOT NULL UNIQUE,
 expira_en DATETIME NOT NULL,
 usado TINYINT DEFAULT 0,
 creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario)
);

INSERT INTO categorias(nombre) VALUES ('Analgésicos'),('Antigripal'),('Controlado'),('Curación');

INSERT INTO usuarios(nombre,correo,telefono,password_hash,id_rol) VALUES
('Mostrador VeraMedica','mostrador@veramedica.com','229234566','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',2),
('Dr. Edgar Reyes','edgar@veramedica.com','2291111111','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',3),
('Dra. Isis Patiño','isis@veramedica.com','2292222222','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',3),
('Marcela Miranda Fuentes','marcela@veramedica.com','2290000001','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',2),
('Mariela','mariela@veramedica.com','2290000002','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',2),
('Ezequiel Ruiz Miranda','ezequiel@veramedica.com','2290000003','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',2),
('Cliente Demo','cliente@veramedica.com','2293333333','$2y$12$D0XvydYwq63.tPMTQ65xdusMeqbSZ6kBCtOJ33SXF4eC9XqDCtzma',1);

INSERT INTO doctores(id_usuario,especialidad) VALUES (2,'Ultrasonido'),(3,'Ginecología');

INSERT INTO servicios(nombre,descripcion,precio,id_doctor) VALUES
('Tiroides','Ultrasonido realizado por Dr. Edgar.',350,1),
('Partes Blandas','Ultrasonido realizado por Dr. Edgar.',300,1),
('Abdomen total','Ultrasonido realizado por Dr. Edgar.',600,1),
('Apendicular','Ultrasonido realizado por Dr. Edgar.',300,1),
('Renal y vías urinarias','Ultrasonido realizado por Dr. Edgar.',350,1),
('Hígado y vías biliares','Ultrasonido realizado por Dr. Edgar.',350,1),
('Pélvico','Servicio ginecológico para ellas.',350,2),
('Endovaginal','Servicio ginecológico para ellas.',400,2),
('Mama','Servicio ginecológico para ellas.',350,2),
('Prostático','Servicio para ellos.',400,1),
('Testicular','Servicio para ellos.',400,1),
('Consulta médica general','Consulta básica en farmacia.',100,1),
('Consulta días festivos','Consulta médica en día festivo.',120,1),
('Toma de presión','Servicio médico general.',25,1),
('Aplicación de inyección','Servicio médico general.',25,1),
('Toma de glucosa','Servicio médico general.',45,1),
('Lavado de oído','Servicio médico general.',80,1),
('Certificado Médico Simple','Servicio médico general.',80,1),
('Consulta médico completo','Servicio médico general.',100,1),
('Revisión de DIU','Servicio médico general.',150,2),
('Retiro de DIU','Servicio médico general.',180,2),
('Colocación de DIU','Servicio médico general.',250,2),
('Curación menor','Servicio médico general.',100,1),
('Curación mayor','Servicio médico general.',150,1),
('Retiro de puntos','Servicio médico general.',80,1),
('Retiro de uña','Servicio médico general.',150,1),
('Retiro de sonda Foley','Servicio médico general.',150,1),
('Colocación de sonda Foley','Servicio médico general.',200,1),
('Retiro de implante','Servicio médico general.',380,1);


INSERT INTO personal_mostrador(id_usuario,nombre,turno,notas) VALUES
(4,'Marcela Miranda Fuentes','Matutino','Lunes a sábado de 9:00 am a 3:00 pm. Atiende ventas y limpieza de sala de ventas y pasillos de consultorios.'),
(5,'Mariela','Vespertino','Domingo cubre todo el horario de servicio. De martes a viernes atiende de 3:00 pm a 9:00 pm.'),
(6,'Ezequiel Ruiz Miranda','Vespertino sábado','Cubre descanso de Mariela los sábados de 3:00 pm a 9:00 pm.');

INSERT INTO horarios_personal(id_personal,dia_semana,hora_inicio,hora_fin) VALUES
(1,'Lunes','09:00:00','15:00:00'),(1,'Martes','09:00:00','15:00:00'),(1,'Miércoles','09:00:00','15:00:00'),(1,'Jueves','09:00:00','15:00:00'),(1,'Viernes','09:00:00','15:00:00'),(1,'Sábado','09:00:00','15:00:00'),
(2,'Martes','15:00:00','21:00:00'),(2,'Miércoles','15:00:00','21:00:00'),(2,'Jueves','15:00:00','21:00:00'),(2,'Viernes','15:00:00','21:00:00'),(2,'Domingo','10:00:00','16:00:00'),
(3,'Sábado','15:00:00','21:00:00');

INSERT INTO horarios_doctores(id_doctor,dia_semana,hora_inicio,hora_fin) VALUES
(1,'Lunes','15:00:00','21:00:00'),(1,'Martes','15:00:00','21:00:00'),(1,'Miércoles','15:00:00','21:00:00'),(1,'Jueves','15:00:00','21:00:00'),(1,'Viernes','15:00:00','21:00:00'),(1,'Sábado','15:00:00','21:00:00'),
(2,'Lunes','09:00:00','15:00:00'),(2,'Martes','09:00:00','15:00:00'),(2,'Miércoles','09:00:00','15:00:00'),(2,'Jueves','09:00:00','15:00:00'),(2,'Viernes','09:00:00','15:00:00');

INSERT INTO productos(nombre,descripcion,precio,stock,imagen,id_categoria) VALUES
('Paracetamol','500 MG 10 TABLETAS',63.00,30,'paracetamol.jpg',1),
('Ibuprofeno','800 mg con 10 Tabletas',72.00,20,'ibuprofeno.jpg',1),
('Vick - Pack Jarabe 44Tos','240 ml',100.00,15,'vick.jpg',2),
('vXI-3 Xtra Antigripal','800 mg con 10 Tabletas',52.00,0,'antigripal.jpg',2),
('Nasalub Solución Adulto','240 ml',150.00,0,'nasalub.jpg',2),
('Clonazepam','2 mg',100.00,10,'clonazepam.jpg',3);

INSERT INTO promociones(titulo,descripcion,descuento,fecha_inicio,fecha_fin) VALUES
('Domingos de ahorro','Todos los domingos 20% de descuento en medicamentos genéricos seleccionados.',20,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 60 DAY));

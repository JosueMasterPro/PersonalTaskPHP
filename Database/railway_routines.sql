-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: URL    Database: Name
-- ------------------------------------------------------
-- Server version	9.3.0


--
-- Dumping routines for database 'railway'
--

DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_actualizarTareas`(
	in p_id int,
	in p_titulo VARCHAR(255),
	in p_descripcion TEXT,
	in p_completada TINYINT(1),
	in p_fecha_final DATE
)
begin
	UPDATE tareas
	SET titulo = p_titulo,
		descripcion = p_descripcion,
		completada = p_completada,
		fecha_final = p_fecha_final
	WHERE id_Tarea = p_id;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_actualizarUsuarioYRol`(
    IN p_id_usuario INT,
    IN p_usuario VARCHAR(50),
    IN p_nombre VARCHAR(50),
    IN p_apellido VARCHAR(50),
    IN p_email VARCHAR(100),
    IN p_activo TINYINT(1),
    IN p_rol VARCHAR(20)
)
BEGIN
    -- Actualiza datos del usuario
    UPDATE usuarios
    SET usuario = p_usuario,
        nombre = p_nombre,
        apellido = p_apellido,
        email = p_email,
        activo = p_activo
    WHERE id = p_id_usuario;
    -- Actualiza el rol del usuario en la tabla roles
    UPDATE roles
    SET rol = p_rol
    WHERE id_usuario = p_usuario;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_EliminarTarea`(IN p_id_Tarea INT)
BEGIN
    DELETE FROM tareas WHERE id_Tarea = p_id_Tarea;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_EliminarUsuario`(
    IN p_id INT)
BEGIN
    DELETE FROM usuarios 
    WHERE id = p_id;
    
    SELECT ROW_COUNT() AS filas_afectadas;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_InsertarTarea`(
    IN p_id_usuario int,
    IN p_titulo VARCHAR(255),
    IN p_tipo VARCHAR(20),
    IN p_descripcion TEXT,
    IN p_completada TINYINT(1),
    IN p_fecha_final DATE
)
BEGIN
    INSERT INTO tareas(id_usuario, titulo, tipo, descripcion, completada, fecha_final)
    VALUES (p_id_usuario, p_titulo, p_tipo ,p_descripcion, p_completada, p_fecha_final);
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_InsertarUsuario`(
    IN p_usuario      VARCHAR(30),
    IN p_nombre       VARCHAR(30),
    IN p_apellido     VARCHAR(30),
    IN p_email        VARCHAR(100),
    IN p_password     VARCHAR(255),      -- password hasheado
    IN p_token        VARCHAR(255)       -- token de verificación
)
BEGIN
    INSERT INTO usuarios (
        usuario, nombre, apellido, email, password,
        token_verificacion, verificado
    ) VALUES (
        p_usuario, p_nombre, p_apellido, p_email, p_password,
        p_token , 0
    );
    SET @nuevo_id = LAST_INSERT_ID();
    INSERT INTO roles (id_usuario, rol) VALUES (p_usuario, 'employee');
    SELECT @nuevo_id AS nuevo_id;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_ObtenerTareasPorRol`(
    IN p_usuario_id VARCHAR(50),
    IN p_es_admin VARCHAR(20),
    IN p_tipo VARCHAR(20)
)
BEGIN
    IF p_es_admin = 'admin' THEN
        SELECT t.*, u.usuario, u.email
        FROM tareas t
        JOIN usuarios u ON t.id_usuario = u.id
        where t.tipo = p_tipo
        ORDER BY t.fecha_creacion DESC;
    ELSE
        SELECT t.*, u.usuario, u.email
        FROM tareas t
        JOIN usuarios u ON t.id_usuario = u.id
        WHERE t.id_usuario = p_usuario_id
          AND t.tipo = p_tipo
        ORDER BY t.fecha_creacion DESC;
    END IF;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_ObtenerUsuarios`()
BEGIN
    SELECT 
        id,
        usuario,
        nombre,
        apellido,
        email,
		verificado,
        activo,
        rol
    FROM usuarios
    JOIN roles ON usuario = id_usuario;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_UsuarioDesactivado`(
    IN p_id INT
)
BEGIN
    UPDATE usuarios
    SET activo = 0
    WHERE id = p_id;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_VerificarCuenta`(
    IN  p_token   VARCHAR(255)
)
BEGIN
    UPDATE usuarios
    SET verificado = 1,
        token_verificacion = NULL
    WHERE token_verificacion = p_token
      AND verificado = 0;

    SELECT ROW_COUNT() AS filas_afectadas;  -- 1 si se verificó, 0 si no
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_VerificarExistenciaUsuario`(
	IN p_id int)
BEGIN
    SELECT 
        id
    FROM usuarios
    WHERE id = p_id;
END ;;
DELIMITER ;


DELIMITER ;;
CREATE DEFINER=`root`@`%` PROCEDURE `sp_VerificarLogin`(
    IN p_usuario VARCHAR(30))
BEGIN
    SELECT 
        id,
        usuario,
        nombre,
        apellido,
        email,
        activo,
        password,
	     verificado,
         rol
    FROM usuarios
    inner join roles on usuario=id_usuario
    WHERE usuario = p_usuario ;
END ;;
DELIMITER ;

-- Dump completed on 2025-06-06 16:05:43

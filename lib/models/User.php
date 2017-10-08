<?php

class User
{
    /**
     * @var int the user's ID
     */
    private $id;
    /**
     * @var string unique username
     */
    private $username;
    /**
     * @var string e-mail address used for registration
     */
    private $email;
    /**
     * @var string base64 one-way hash of the user's password
     */
    private $password_hash;

    /**
     * User constructor.
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string $password_hash
     */
    private function __construct($id, $username, $email, $password_hash)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $password_hash;
    }

    /**
     * Create a new user and save it into the database.
     * @param string $username username of new user; must be unique among all users
     * @param string $email contact e-mail address for new user; must be unique among all users
     * @param string $password plain text password for the new user
     * @return User user object
     * @throws UserException if user creation fails
     */
    public static function create($username, $email, $password)
    {
        global $db;
        $password_hash = self::hash_password($password);
        $stmt = $db->prepare("INSERT INTO users(username, email, password_hash) VALUES (:user, :email, :pass)");
        try {
            $stmt->bindValue(":user", $username);
            $stmt->bindValue(":email", $email);
            $stmt->bindValue(":pass", $password_hash);
            $stmt->execute();

            $uid = (int) $db->lastInsertId();

            log_info(sprintf("Created user #%d: %s <%s>", $uid, $username, $email));
            return new User($uid, $username, $email, $password_hash);
        } catch (PDOException $e) {
            if ($e->errorInfo[0] == '23000' && stripos($e->errorInfo[2], "unique") !== false) {
                if (strpos($e->errorInfo[2], "email") !== false) {
                    throw new UserException(ERROR_EMAIL_EXISTS, $e);
                }
                if (strpos($e->errorInfo[2], "username") !== false) {
                    throw new UserException(ERROR_USERNAME_EXISTS, $e);
                }
            }
            throw new UserException(ERROR_USER_UNKNOWN, $e);
        }
    }

    /**
     * get a user from the database
     * @param $user_id
     * @return User by ID
     */
    public static function getById($user_id)
    {
        global $db;

        $stmt = $db->prepare("SELECT id, username, email, password_hash FROM users WHERE id = :user_id");
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return new User($user['id'], $user['username'], $user['email'], $user['password_hash']);
    }

    /**
     * @return int unique ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string unique username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string registered e-mail address
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Hash the given password and encode it as a base64 string.
     * @param string $password plain text password
     * @return string password hash
     */
    public static function hash_password($password)
    {
        //TODO: hash password
        return base64_encode($password);
    }
}

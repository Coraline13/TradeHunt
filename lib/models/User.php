<?php
require_once dirname(__FILE__) . '/../password.php';

/**
 * A user represents login information and identity for a person's account. Users can interact with listings,
 * make trades and in general perform actions on the site.
 *
 * @see Session
 * @see Listing
 */
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
     * @param string $hashed_password
     */
    private function __construct($id, $username, $email, $hashed_password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $hashed_password;
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
        $hashed_password = self::hash_password($password);
        try {
            $stmt = $db->prepare("INSERT INTO users(username, email, password_hash) VALUES (:user, :email, :pass)");
            $stmt->bindValue(":user", $username, PDO::PARAM_STR);
            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->bindValue(":pass", $hashed_password, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();

            $uid = (int)$db->lastInsertId();

            log_info(sprintf("Created user #%d: %s <%s>", $uid, $username, $email));
            return new User($uid, $username, $email, $hashed_password);
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
     * Get a user from the database.
     * @param int $user_id
     * @return User user by ID
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
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

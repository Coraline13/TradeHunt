<?php
require_once dirname(__FILE__) . '/../password.php';

/**
 * A user represents login information and identity for a person's account. Users can interact with listings,
 * make trades and in general perform actions on the site.
 *
 * @see Session
 * @see Listing
 * @see Profile
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
     * @var int the profile's ID
     * @see Profile
     */
    private $profile_id;

    /**
     * User constructor.
     * @param int $id
     * @param string $username
     * @param string $email
     * @param string $hashed_password
     * @param int $profile_id
     */
    private function __construct($id, $username, $email, $hashed_password, $profile_id)
    {
        $this->id = require_non_empty($id, "user_id");
        $this->username = require_non_empty($username, "username");
        $this->email = require_non_empty($email, "email");
        $this->password_hash = require_non_empty($hashed_password, "hashed_password");
        $this->profile_id = require_non_empty($profile_id, "profile_id");
    }

    /**
     * @param array $u array result fetched with PDO::FETCH_ASSOC
     * @return User User object
     */
    public static function makeFromPDO($u)
    {
        return new User($u['id'], $u['username'], $u['email'], $u['password_hash'], $u['profile_id']);
    }

    /**
     * Create a new user and save it into the database.
     * @param string $username username of new user; must be unique among all users
     * @param string $email contact e-mail address for new user; must be unique among all users
     * @param string $password plain text password for the new user
     * @param Profile $profile profile information for the new user
     * @return User user object
     * @throws UserException if user creation fails
     */
    public static function create($username, $email, $password, Profile $profile)
    {
        global $db;
        $hashed_password = self::hash_password($password);
        try {
            $stmt = $db->prepare("INSERT INTO users(username, email, password_hash, profile_id) VALUES (:user, :email, :pass, :profile_id)");
            $stmt->bindValue(":user", $username, PDO::PARAM_STR);
            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->bindValue(":pass", $hashed_password, PDO::PARAM_STR);
            $stmt->bindValue(":profile_id", $profile->getId(), PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();

            $uid = (int)$db->lastInsertId();

            log_info(sprintf("Created user #%d: %s <%s>", $uid, $username, $email));
            return new User($uid, $username, $email, $hashed_password, $profile->getId());
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

        $stmt = $db->prepare("SELECT id, username, email, password_hash, profile_id FROM users WHERE id = :user_id");
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return self::makeFromPDO(require_fetch_one($stmt, "User", "id", $user_id));
    }

    /**
     * Look for a user in the database, matching either name or email.
     * @param string $username_or_email lookup string
     * @return User found user
     * @throws Exception unkown error
     * @throws UserException if the user cannot be found
     */
    public static function getByNameOrEmail($username_or_email)
    {
        try {
            global $db;

            $stmt = $db->prepare("SELECT id, username, email, password_hash, profile_id FROM users WHERE username = :lookup OR email = :lookup");
            $stmt->bindValue(":lookup", $username_or_email, PDO::PARAM_STR);
            $stmt->execute();

            return self::makeFromPDO(require_fetch_one($stmt, "User", "name or email", $username_or_email));
        } catch (Exception $e) {
            if ($e instanceof APIException || $e instanceof PDOException) {
                throw new UserException(ERROR_USER_NOT_FOUND, $e, _t(null, STRING_USER_NOT_FOUND, $username_or_email));
            }
            throw $e;
        }
    }

    /**
     * Authenticate the user against the given password.
     * @param string $password plain text password
     * @return Session the newly opened session
     * @throws UserException if authentication fails (e.g. the password is wrong)
     */
    public function authenticate($password)
    {
        if (password_verify($password, $this->password_hash)) {
            return $this->openSession();
        }
        throw new UserException(ERROR_WRONG_PASSWORD, null);
    }

    /**
     * Opens a session for the user without checking password.
     *
     * USE WITH CAUTION!
     * @return Session the newly opened session
     */
    public function openSession() {
        $exp = new DateTime();
        $exp->add(DateInterval::createFromDateString('2 weeks'));
        $session = Session::create($this, $exp);
        setcookie(CFG_COOKIE_AUTH, $session->getToken(), $exp->getTimestamp(), "/", "", $GLOBALS['secure'], true);
        return $session;
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

    /**
     * @return Profile user's profile
     */
    public function getProfile()
    {
        return Profile::getById($this->profile_id);
    }

    /**
     * @return Bookmark[] array of all bookmarks of current user
     */
    public function getBookmarks()
    {
        global $db;

        $stmt = $db->prepare("SELECT id, user_id, listing_id, added FROM bookmarks WHERE bookmarks.user_id = :user_id");
        $stmt->bindValue(":user_id", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        $bookmarks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bookmarks as $bookmark) {
            $result[] = Bookmark::makeFromPDO($bookmark);
        }

        return $result;
    }
}

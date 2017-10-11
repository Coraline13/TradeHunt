<?php

/**
 * A session is the backing database entity for user authentication cookies.
 *
 * Every time a user pefrosm a login, a random token is generated and stored in both the `sessions` table and as a
 * cookie in the user's browser.
 *
 * On subsequent actions, the authentication cookie is checked against the stored token, and the session is
 * either valid or not.
 *
 * @see User
 */
class Session
{
    /**
     * @var int the session's ID
     */
    private $id;
    /**
     * @var int the user's ID
     * @see User
     */
    private $user_id;
    /**
     * @var string unique random generated token
     */
    private $token;
    /**
     * @var DateTime token expiration date
     */
    private $expiration;

    /**
     * Session constructor.
     * @param int $id
     * @param int $user_id
     * @param string $token
     * @param DateTime $expiration
     */
    private function __construct($id, $user_id, $token, DateTime $expiration)
    {
        $this->id = require_non_empty($id, "session_id");
        $this->user_id = require_non_empty($user_id, "user_id");
        $this->token = require_non_empty($token, "token");
        $this->expiration = require_non_null($expiration, "expiration");
    }

    /**
     * Create a new session and save it into the database.
     * @param User $user associated user
     * @param DateTime $expiration session validity
     * @return Session session object
     * @throws APIException if session create fails
     */
    public static function create(User $user, DateTime $expiration)
    {
        global $db;
        $token = bin2hex(openssl_random_pseudo_bytes(10));
        try {
            $stmt = $db->prepare("INSERT INTO sessions(user_id, token, expiration) VALUES (:user_id, :token, :expiration)");
            $stmt->bindValue(":user_id", $user->getId(), PDO::PARAM_INT);
            $stmt->bindValue(":token", $token, PDO::PARAM_STR);
            $stmt->bindValue(":expiration", $expiration->format('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmt->execute();

            $sid = (int)$db->lastInsertId();

            log_debug(sprintf("Opened session %d for user #%s (%d)", $sid, $user->getUsername(), $user->getId()));
            return new Session($sid, $user->getId(), $token, $expiration);
        } catch (PDOException $e) {
            throw new APIException(ERROR_GENERIC_API, $e, "fail to create session");
        }
    }

    /**
     * @return User the user authenticated by this session
     */
    public function getUser()
    {
        return User::getById($this->user_id);
    }

    /**
     * @return int the session's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string unique random generated token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return DateTime token expiration date
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}

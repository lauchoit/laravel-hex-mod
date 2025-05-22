<?php


namespace App\Http\Utilities;


class ApiResponse
{
    /**
     * RESPONSES SUCCESS
     */
    public static $EMAIL_PASSWORD_RESENT = 'success.email.password.reset';
    public static $PASSWORD_CHANGED = 'success.password.changed';
    public static $CHANNEL_CREATED = 'success.channel.created';
    public static $EMAIL_VERIFIED_SENT = 'success.email.verified.sent';
    public static $ACCOUNT_VERIFIED = 'success.account.verified';
    public static $DELETE_SUCCESS = 'success.delete';
    public static $USER_UPDATED = 'success.user.updated';
    public static $UPDATE_DATA_SUCCESS = 'update.data.success';
    public static $CHANNEL_UPDATED = 'success.channel.updated';
    public static $SEARCH_SUCCESS = 'success.search';
    public static $STORE_DATA_SUCCESS = 'success.store.data';
    public static $CHANNEL_FOLLOW = 'success.channel.follow';
    public static $UNCHANNEL_FOLLOW = 'success.channel.unfollow';
    public static $CHANNEL_BLOCK = 'success.channel.blocked';
    public static $CHANNEL_UNBLOCK_FOR_YOU = 'success.channel.unblocked';
    public static $CHANNEL_NOT_BLOCK_FOR_YOU = 'success.channel.not.blocked';
    public static $ALREADY_FOLLOWING = 'success.channel.already.following';
    public static $PENDING_CANCELED = 'pending.cancel.subscription';
    public static $STORE_PLAN_SMALL_SELECTED = 'success.plan.small.change';
    public static $ACCOUNT_CREATED = 'success.bank_account.create';
    public static $ACCOUNT_UPDATED = 'success.bank_account.updated';
    public static $MUST_ACCOUNT_CREATED = 'success.account.must.account.created';
    public static $ACCOUNT_STRIPE_DELETED = 'success.account.stripe.deleted';
    public static $VIDEO_DELETE_AFTER_FINISH = 'success.video.delete.after.finish';
    public static $VIDEO_DELETE = 'success.video.delete';
    public static $VIDEO_STORED = 'success.video.stored';
    public static $VIDEO_ON_AIR = 'success.video.onair';
    public static string $PRORATED_CALCULATE = 'success.prorated.calculate';
    public static string $NO_PAID_FOLLOW = 'success.before.paid.channel';
    public static string $LOGOUT_SUCCESS = 'success.logout';
    /**
     * RESPONSES ERRORS
     */
    public static $NOT_EMAIL = 'error.non.existent.email';
    public static $REFERENCE_EXIST    = 'error.reference.exist';
    public static $ERROR_PROCESSING_STRIPE = 'error.processing.stripe';
    public static $ERROR_STORAGE_PAID = 'error.storage.paid';
    public static $INVALID_TOKEN = 'error.invalid.token';
    public static $ACCOUNT_NOT_EXIST = 'error.account.non.exist';
    public static $CHANNEL_NOT_EXIST = 'error.channel.non.exist';
    public static $VIDEO_NOT_EXIST = 'error.video.non.exist';
    public static $DONT_HAVE_CHANNEL = 'error.non.existent.channel';
    public static $INVALID_CREDENTIALS = 'error.invalid.credentials';
    public static $SERVER_ERROR = 'error.server';
    public static $NOT_FOLLOW_CHANNEL = 'error.not.following.channel';
    public static $STORE_DATA_ERROR = 'error.store.data';
    public static $HAVE_A_PLAN = 'error.store.has.a.plan';
    public static $ERROR_DELETING_ACCOUNT_STRIPE = 'error.deleting.account.stripe';
    public static $ERROR_CREATING_ACCOUNT = 'error.bank_account.creating';
    public static $ERROR_NO_PERMISSION = 'error.you.do.not.have.permission';
    public static $ERROR_UNAUTHORIZED  = 'error.unauthorized';
    public static $ERROR_IS_IT_ALREADY_PUBLISHED  = 'error.is.it.already.published';
    public static $ERROR_NOT_FOUND = 'error.not_found';
    public static $HAVE_A_EVENT = 'error.store.has.a.event';
    public static $EXIST_ON_EVENT = 'error.exists.event';

    public static $HAVE_RECEIPT = 'error.receipt.event';

    public static $CHANNEL_NAME_RESERVED = 'error.channel.name.reserved';

    public static function success(string $message, mixed $data = null, $code = 200)
    {
        return self::responseGeneric($message, $data, $code, $ok = true);
    }

    public static function error(string $message, mixed $data = null, $code = 400)
    {
        return self::responseGeneric($message, $data, $code, $ok = false);
    }

    private static function responseGeneric($message, $data, $code, $ok) {
        return response()->json([
            'ok' => $ok,
            'message' => $message,
            'data' => $data
        ], $code);
    }

}

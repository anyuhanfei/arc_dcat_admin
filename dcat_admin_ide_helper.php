<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection sortable
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection is_enabled
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection key
     * @property Grid\Column|Collection expiration
     * @property Grid\Column|Collection owner
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection total_jobs
     * @property Grid\Column|Collection pending_jobs
     * @property Grid\Column|Collection failed_jobs
     * @property Grid\Column|Collection failed_job_ids
     * @property Grid\Column|Collection cancelled_at
     * @property Grid\Column|Collection finished_at
     * @property Grid\Column|Collection attempts
     * @property Grid\Column|Collection reserved_at
     * @property Grid\Column|Collection available_at
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection expires_at
     * @property Grid\Column|Collection ip_address
     * @property Grid\Column|Collection user_agent
     * @property Grid\Column|Collection last_activity
     * @property Grid\Column|Collection content
     * @property Grid\Column|Collection deleted_at
     * @property Grid\Column|Collection platform
     * @property Grid\Column|Collection version_code
     * @property Grid\Column|Collection wgt
     * @property Grid\Column|Collection apk
     * @property Grid\Column|Collection store_url
     * @property Grid\Column|Collection force_update
     * @property Grid\Column|Collection is_show
     * @property Grid\Column|Collection sort
     * @property Grid\Column|Collection image
     * @property Grid\Column|Collection category_id
     * @property Grid\Column|Collection author
     * @property Grid\Column|Collection summary
     * @property Grid\Column|Collection keywords
     * @property Grid\Column|Collection status
     * @property Grid\Column|Collection position
     * @property Grid\Column|Collection link_type
     * @property Grid\Column|Collection link_value
     * @property Grid\Column|Collection question
     * @property Grid\Column|Collection answer
     * @property Grid\Column|Collection notice_id
     * @property Grid\Column|Collection openid
     * @property Grid\Column|Collection session_key
     * @property Grid\Column|Collection unionid
     * @property Grid\Column|Collection nickname
     * @property Grid\Column|Collection raw
     * @property Grid\Column|Collection currency
     * @property Grid\Column|Collection amount
     * @property Grid\Column|Collection before_balance
     * @property Grid\Column|Collection after_balance
     * @property Grid\Column|Collection remark
     * @property Grid\Column|Collection ref1
     * @property Grid\Column|Collection ref2
     * @property Grid\Column|Collection balance
     * @property Grid\Column|Collection account
     * @property Grid\Column|Collection phone
     * @property Grid\Column|Collection signature
     * @property Grid\Column|Collection gender
     * @property Grid\Column|Collection birthday
     * @property Grid\Column|Collection phone_verified_at
     * @property Grid\Column|Collection email_verified_at
     *
     * @method Grid\Column|Collection sortable(string $label = null)
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection key(string $label = null)
     * @method Grid\Column|Collection expiration(string $label = null)
     * @method Grid\Column|Collection owner(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection total_jobs(string $label = null)
     * @method Grid\Column|Collection pending_jobs(string $label = null)
     * @method Grid\Column|Collection failed_jobs(string $label = null)
     * @method Grid\Column|Collection failed_job_ids(string $label = null)
     * @method Grid\Column|Collection cancelled_at(string $label = null)
     * @method Grid\Column|Collection finished_at(string $label = null)
     * @method Grid\Column|Collection attempts(string $label = null)
     * @method Grid\Column|Collection reserved_at(string $label = null)
     * @method Grid\Column|Collection available_at(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection expires_at(string $label = null)
     * @method Grid\Column|Collection ip_address(string $label = null)
     * @method Grid\Column|Collection user_agent(string $label = null)
     * @method Grid\Column|Collection last_activity(string $label = null)
     * @method Grid\Column|Collection content(string $label = null)
     * @method Grid\Column|Collection deleted_at(string $label = null)
     * @method Grid\Column|Collection platform(string $label = null)
     * @method Grid\Column|Collection version_code(string $label = null)
     * @method Grid\Column|Collection wgt(string $label = null)
     * @method Grid\Column|Collection apk(string $label = null)
     * @method Grid\Column|Collection store_url(string $label = null)
     * @method Grid\Column|Collection force_update(string $label = null)
     * @method Grid\Column|Collection is_show(string $label = null)
     * @method Grid\Column|Collection sort(string $label = null)
     * @method Grid\Column|Collection image(string $label = null)
     * @method Grid\Column|Collection category_id(string $label = null)
     * @method Grid\Column|Collection author(string $label = null)
     * @method Grid\Column|Collection summary(string $label = null)
     * @method Grid\Column|Collection keywords(string $label = null)
     * @method Grid\Column|Collection status(string $label = null)
     * @method Grid\Column|Collection position(string $label = null)
     * @method Grid\Column|Collection link_type(string $label = null)
     * @method Grid\Column|Collection link_value(string $label = null)
     * @method Grid\Column|Collection question(string $label = null)
     * @method Grid\Column|Collection answer(string $label = null)
     * @method Grid\Column|Collection notice_id(string $label = null)
     * @method Grid\Column|Collection openid(string $label = null)
     * @method Grid\Column|Collection session_key(string $label = null)
     * @method Grid\Column|Collection unionid(string $label = null)
     * @method Grid\Column|Collection nickname(string $label = null)
     * @method Grid\Column|Collection raw(string $label = null)
     * @method Grid\Column|Collection currency(string $label = null)
     * @method Grid\Column|Collection amount(string $label = null)
     * @method Grid\Column|Collection before_balance(string $label = null)
     * @method Grid\Column|Collection after_balance(string $label = null)
     * @method Grid\Column|Collection remark(string $label = null)
     * @method Grid\Column|Collection ref1(string $label = null)
     * @method Grid\Column|Collection ref2(string $label = null)
     * @method Grid\Column|Collection balance(string $label = null)
     * @method Grid\Column|Collection account(string $label = null)
     * @method Grid\Column|Collection phone(string $label = null)
     * @method Grid\Column|Collection signature(string $label = null)
     * @method Grid\Column|Collection gender(string $label = null)
     * @method Grid\Column|Collection birthday(string $label = null)
     * @method Grid\Column|Collection phone_verified_at(string $label = null)
     * @method Grid\Column|Collection email_verified_at(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection sortable
     * @property Show\Field|Collection id
     * @property Show\Field|Collection name
     * @property Show\Field|Collection type
     * @property Show\Field|Collection version
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection is_enabled
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection value
     * @property Show\Field|Collection username
     * @property Show\Field|Collection password
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection key
     * @property Show\Field|Collection expiration
     * @property Show\Field|Collection owner
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection total_jobs
     * @property Show\Field|Collection pending_jobs
     * @property Show\Field|Collection failed_jobs
     * @property Show\Field|Collection failed_job_ids
     * @property Show\Field|Collection cancelled_at
     * @property Show\Field|Collection finished_at
     * @property Show\Field|Collection attempts
     * @property Show\Field|Collection reserved_at
     * @property Show\Field|Collection available_at
     * @property Show\Field|Collection email
     * @property Show\Field|Collection token
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection expires_at
     * @property Show\Field|Collection ip_address
     * @property Show\Field|Collection user_agent
     * @property Show\Field|Collection last_activity
     * @property Show\Field|Collection content
     * @property Show\Field|Collection deleted_at
     * @property Show\Field|Collection platform
     * @property Show\Field|Collection version_code
     * @property Show\Field|Collection wgt
     * @property Show\Field|Collection apk
     * @property Show\Field|Collection store_url
     * @property Show\Field|Collection force_update
     * @property Show\Field|Collection is_show
     * @property Show\Field|Collection sort
     * @property Show\Field|Collection image
     * @property Show\Field|Collection category_id
     * @property Show\Field|Collection author
     * @property Show\Field|Collection summary
     * @property Show\Field|Collection keywords
     * @property Show\Field|Collection status
     * @property Show\Field|Collection position
     * @property Show\Field|Collection link_type
     * @property Show\Field|Collection link_value
     * @property Show\Field|Collection question
     * @property Show\Field|Collection answer
     * @property Show\Field|Collection notice_id
     * @property Show\Field|Collection openid
     * @property Show\Field|Collection session_key
     * @property Show\Field|Collection unionid
     * @property Show\Field|Collection nickname
     * @property Show\Field|Collection raw
     * @property Show\Field|Collection currency
     * @property Show\Field|Collection amount
     * @property Show\Field|Collection before_balance
     * @property Show\Field|Collection after_balance
     * @property Show\Field|Collection remark
     * @property Show\Field|Collection ref1
     * @property Show\Field|Collection ref2
     * @property Show\Field|Collection balance
     * @property Show\Field|Collection account
     * @property Show\Field|Collection phone
     * @property Show\Field|Collection signature
     * @property Show\Field|Collection gender
     * @property Show\Field|Collection birthday
     * @property Show\Field|Collection phone_verified_at
     * @property Show\Field|Collection email_verified_at
     *
     * @method Show\Field|Collection sortable(string $label = null)
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection key(string $label = null)
     * @method Show\Field|Collection expiration(string $label = null)
     * @method Show\Field|Collection owner(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection total_jobs(string $label = null)
     * @method Show\Field|Collection pending_jobs(string $label = null)
     * @method Show\Field|Collection failed_jobs(string $label = null)
     * @method Show\Field|Collection failed_job_ids(string $label = null)
     * @method Show\Field|Collection cancelled_at(string $label = null)
     * @method Show\Field|Collection finished_at(string $label = null)
     * @method Show\Field|Collection attempts(string $label = null)
     * @method Show\Field|Collection reserved_at(string $label = null)
     * @method Show\Field|Collection available_at(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection expires_at(string $label = null)
     * @method Show\Field|Collection ip_address(string $label = null)
     * @method Show\Field|Collection user_agent(string $label = null)
     * @method Show\Field|Collection last_activity(string $label = null)
     * @method Show\Field|Collection content(string $label = null)
     * @method Show\Field|Collection deleted_at(string $label = null)
     * @method Show\Field|Collection platform(string $label = null)
     * @method Show\Field|Collection version_code(string $label = null)
     * @method Show\Field|Collection wgt(string $label = null)
     * @method Show\Field|Collection apk(string $label = null)
     * @method Show\Field|Collection store_url(string $label = null)
     * @method Show\Field|Collection force_update(string $label = null)
     * @method Show\Field|Collection is_show(string $label = null)
     * @method Show\Field|Collection sort(string $label = null)
     * @method Show\Field|Collection image(string $label = null)
     * @method Show\Field|Collection category_id(string $label = null)
     * @method Show\Field|Collection author(string $label = null)
     * @method Show\Field|Collection summary(string $label = null)
     * @method Show\Field|Collection keywords(string $label = null)
     * @method Show\Field|Collection status(string $label = null)
     * @method Show\Field|Collection position(string $label = null)
     * @method Show\Field|Collection link_type(string $label = null)
     * @method Show\Field|Collection link_value(string $label = null)
     * @method Show\Field|Collection question(string $label = null)
     * @method Show\Field|Collection answer(string $label = null)
     * @method Show\Field|Collection notice_id(string $label = null)
     * @method Show\Field|Collection openid(string $label = null)
     * @method Show\Field|Collection session_key(string $label = null)
     * @method Show\Field|Collection unionid(string $label = null)
     * @method Show\Field|Collection nickname(string $label = null)
     * @method Show\Field|Collection raw(string $label = null)
     * @method Show\Field|Collection currency(string $label = null)
     * @method Show\Field|Collection amount(string $label = null)
     * @method Show\Field|Collection before_balance(string $label = null)
     * @method Show\Field|Collection after_balance(string $label = null)
     * @method Show\Field|Collection remark(string $label = null)
     * @method Show\Field|Collection ref1(string $label = null)
     * @method Show\Field|Collection ref2(string $label = null)
     * @method Show\Field|Collection balance(string $label = null)
     * @method Show\Field|Collection account(string $label = null)
     * @method Show\Field|Collection phone(string $label = null)
     * @method Show\Field|Collection signature(string $label = null)
     * @method Show\Field|Collection gender(string $label = null)
     * @method Show\Field|Collection birthday(string $label = null)
     * @method Show\Field|Collection phone_verified_at(string $label = null)
     * @method Show\Field|Collection email_verified_at(string $label = null)
     */
    class Show {}

    /**
     * @method \App\Admin\Form\Field\MediaSelector mediaSelector(...$params)
     * @method \Weiaibaicai\Ueditor\Form\UEditor ueditor(...$params)
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}

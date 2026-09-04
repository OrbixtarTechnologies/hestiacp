# Mail queue

OrbixPanel administrators can inspect delayed Exim deliveries and act on one message at a time from **Server Manager → Mail Queue**.

The queue lists up to 250 messages with their Exim ID, age, size, envelope sender, recipients, and delivery state. A frozen message is one that Exim will not retry automatically until its state changes or an administrator intervenes.

## Inspect a message

Select **Inspect** to view the queued message headers and Exim delivery log. OrbixPanel intentionally excludes the message body so routine delivery triage does not expose message content.

Headers and delivery logs can still contain email addresses, server addresses, and routing details. Limit administrator access and treat this information as private operational data.

## Retry delivery

Select **Retry Delivery** to ask Exim to attempt delivery immediately. The message remains queued if the destination still rejects or defers it. Review the refreshed queue and delivery log for the result.

## Delete a message

Deleting a queued message permanently removes it from Exim. The detail screen requires an explicit acknowledgement before enabling this action. Deleted messages cannot be recovered from the queue.

## Security boundaries

Mail Queue is available only to a server administrator in the administrator context and is hidden while impersonating a hosting account. Message IDs are validated before reaching Exim, all actions use CSRF-protected requests, and retry or deletion events are written to the OrbixPanel system activity log.

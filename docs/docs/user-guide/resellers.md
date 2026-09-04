# Reseller accounts

OrbixPanel resellers can provision and operate their own customer accounts without receiving server-administrator access. This is a least-privilege capability: a reseller remains a regular user and can only act through the owner-scoped Reseller Center.

## Enable a reseller

1. Sign in as an administrator and open **Accounts**.
2. Edit the user who will become a reseller. The user must keep the **User** role.
3. Enable **Allow this user to manage customer accounts**.
4. Set the maximum number of customer accounts and select the hosting packages the reseller may assign.
5. Save the user.

The root administrator and administrator-role accounts cannot become resellers. Reseller nesting is also disabled, so every customer relationship has one direct reseller owner.

## Reseller Center

Enabled resellers receive a **Customers** navigation item. The Reseller Center shows account capacity, active and suspended totals, package scope, and every directly owned customer.

From this page a reseller can:

- create a customer using an allowed hosting package;
- suspend or resume an owned customer account;
- permanently delete an owned customer account.

Every operation checks ownership again in the server command layer. Changing a browser request cannot grant access to another reseller's customers.

## Disable a reseller

A reseller cannot be disabled while it still owns customer accounts. Delete or reassign those accounts first, then turn off reseller delegation from the user's administrator edit page.

## Security boundary

Resellers cannot access server configuration, services, firewall rules, IP administration, package editing, migrations, server health, the system mail queue, other users, or administrator APIs. Package allow-lists and customer-account caps are enforced during provisioning, including concurrent requests.

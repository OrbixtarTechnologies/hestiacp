# Server health

OrbixPanel gives server administrators a WHM-style health view for spotting capacity and availability problems before they affect hosted accounts.

Open **Server Manager → Server Health** to review:

- one-, five-, and fifteen-minute load averages relative to the server's CPU core count;
- current memory use and available memory;
- root filesystem use and remaining capacity;
- panel uptime and operating-system details;
- the running or stopped state of every service monitored by OrbixPanel.

## Health thresholds

The overview highlights CPU load at 70% of one-minute capacity, memory at 75%, and root disk use at 70%. It marks the server as needing attention when CPU or memory reaches 90%, root disk use reaches 85%, or a monitored service stops.

These indicators are operational signals, not a replacement for historical monitoring. Use **Task Monitor** for trend charts and **Service Manager** to inspect, start, stop, or restart individual services.

## Access control

Server Health is available only to a server administrator in the administrator context. It is hidden while an administrator is impersonating a hosting account. The page performs read-only checks and links to existing authenticated management workflows for changes.

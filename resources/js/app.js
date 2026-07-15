import "./bootstrap";

const umamiPendingEvents = [];

const sanitizeUmamiData = (data = {}) => {
    if (!data || typeof data !== "object" || Array.isArray(data)) {
        return undefined;
    }

    const cleaned = Object.entries(data).reduce((result, [key, value]) => {
        if (!/^[a-z0-9_]+$/.test(key)) {
            return result;
        }

        if (
            typeof value === "string" ||
            typeof value === "number" ||
            typeof value === "boolean"
        ) {
            result[key] = value;
        }

        return result;
    }, {});

    return Object.keys(cleaned).length ? cleaned : undefined;
};

const flushUmamiQueue = () => {
    if (
        typeof window === "undefined" ||
        typeof window.umami?.track !== "function"
    ) {
        return;
    }

    while (umamiPendingEvents.length > 0) {
        const next = umamiPendingEvents.shift();
        if (!next) {
            continue;
        }

        if (next.data && Object.keys(next.data).length > 0) {
            window.umami.track(next.name, next.data);
        } else {
            window.umami.track(next.name);
        }
    }
};

window.trackUmamiEvent = (name, data = {}) => {
    if (typeof name !== "string" || name.length === 0) {
        return;
    }

    const payload = {
        name,
        data: sanitizeUmamiData(data),
    };

    if (typeof window.umami?.track === "function") {
        if (payload.data) {
            window.umami.track(payload.name, payload.data);
        } else {
            window.umami.track(payload.name);
        }

        return;
    }

    umamiPendingEvents.push(payload);
};

window.addEventListener("umami-track", (event) => {
    const detail = event.detail ?? {};

    if (!detail.name) {
        return;
    }

    window.trackUmamiEvent(detail.name, detail.data ?? {});
});

window.addEventListener("load", flushUmamiQueue, { once: true });

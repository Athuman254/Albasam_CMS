const functions = {
    buildUrl: (baseUrl, params) => {
        const url = new URL(baseUrl);
        const appendNestedParams = (prefix, obj) => {
            for (const [key, value] of Object.entries(obj)) {
                if (typeof value === "object" && value !== null) {
                    appendNestedParams(`${prefix}[${key}]`, value);
                } else {
                    url.searchParams.append(`${prefix}[${key}]`, value);
                }
            }
        };
        for (const [key, value] of Object.entries(params)) {
            if (typeof value === "object" && value !== null) {
                appendNestedParams(key, value);
            } else {
                url.searchParams.append(key, value);
            }
        }
        return url.toString();
    },
};
export default functions;

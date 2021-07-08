module.exports = {
    root: true,
    modules: {
        "bem-tools": {
            plugins: {
                create: {
                    techs: ["html", "scss", "js"],
                    templateFolder: '.bem/templates',
                    levels: {
                        "src/blocks/modules": {
                            default: true
                        }
                    },
                }
            }
        }
    }
};
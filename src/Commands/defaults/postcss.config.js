import postcssPresetEnv from 'postcss-preset-env';
import postcssNested from 'postcss-nested';
import postcssDiscardComments from 'postcss-discard-comments';

export default {
    plugins: [
        postcssPresetEnv({
            stage: 4,
            browsers: 'last 2 versions',
        }),
        postcssNested,
        postcssDiscardComments,
    ]
}
import { createRouter, createWebHistory } from 'vue-router';
import routes from '@/routes/routes';
import globalGuards from '@/guards/global'
import Query from '@/objects/Query';

const router = createRouter({
    history: createWebHistory(),
    parseQuery: Query.parseQuery,
    stringifyQuery: Query.stringifyQuery,
    routes
});

router.beforeEach(globalGuards);

export default router;

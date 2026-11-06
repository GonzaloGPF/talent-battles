import { useCompany } from '@/stores/company';

export default async () => {
    if (! useCompany().isLoaded) {
        try {
            await useCompany().fetchData();
        } catch (e) {
            return false
        }
    }
}

import { Head, Link } from '@inertiajs/react';
import { Mail, Pencil, Phone, Store } from 'lucide-react';
import Avatar from '@/components/ui/avatar';
import Button from '@/components/ui/button';
import Card, { CardBody, CardHeader } from '@/components/ui/card';
import DescriptionList from '@/components/ui/description-list';
import EmptyState from '@/components/ui/empty-state';
import PageHeader from '@/components/ui/page-header';
import StatusBadge from '@/components/ui/status-badge';
import { formatDateTime } from '@/lib/format';

export default function ShowVendorProfile({ profile, canManage }) {
    const editAction = canManage && (
        <Button as={Link} href={route('vendor-profile.edit')} icon={Pencil}>
            {profile ? 'Edit profile' : 'Set up profile'}
        </Button>
    );

    return (
        <>
            <Head title="Vendor profile" />

            <PageHeader
                title="Vendor profile"
                description="Your catering business details, shown on the app, statements and reports."
                actions={profile && editAction}
            />

            {profile ? (
                <ProfileDetails profile={profile} />
            ) : (
                <Card>
                    <EmptyState
                        icon={Store}
                        title="Your business profile is not set up yet"
                        description={
                            canManage
                                ? 'Add your business name, contact details and logo to get started.'
                                : 'A vendor admin needs to add the business details.'
                        }
                        action={editAction}
                    />
                </Card>
            )}
        </>
    );
}

function ProfileDetails({ profile }) {
    return (
        <div className="space-y-6">
            <Card>
                <CardBody className="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <Avatar
                        name={profile.business_name}
                        src={profile.logo_url}
                        size="xl"
                        rounded="xl"
                        className="ring-1 ring-slate-200"
                    />
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <h2 className="text-xl font-bold text-slate-900">{profile.business_name}</h2>
                            <StatusBadge status={profile.status} label={profile.status_label} />
                        </div>
                        {profile.organization_name && <p className="mt-1 text-sm text-slate-600">{profile.organization_name}</p>}
                        {profile.business_type && <p className="mt-1 text-sm text-slate-500">{profile.business_type}</p>}
                    </div>
                    <div className="flex flex-col gap-2 sm:items-end">
                        <a
                            href={`tel:${profile.phone}`}
                            className="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-slate-700 hover:text-primary-700 sm:min-h-0"
                        >
                            <Phone className="size-4 text-slate-400" aria-hidden="true" />
                            {profile.phone}
                        </a>
                        <a
                            href={`mailto:${profile.email}`}
                            className="inline-flex min-h-11 items-center gap-2 text-sm font-medium break-all text-slate-700 hover:text-primary-700 sm:min-h-0"
                        >
                            <Mail className="size-4 shrink-0 text-slate-400" aria-hidden="true" />
                            {profile.email}
                        </a>
                    </div>
                </CardBody>
            </Card>

            <div className="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader title="Owner & contact" />
                    <CardBody>
                        <DescriptionList
                            items={[
                                { label: 'Owner name', value: profile.owner_name },
                                { label: 'Phone', value: profile.phone },
                                { label: 'Email', value: profile.email, wide: true },
                            ]}
                        />
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Location" />
                    <CardBody>
                        <DescriptionList
                            items={[
                                { label: 'Address', value: profile.address, wide: true },
                                { label: 'City', value: profile.city },
                            ]}
                        />
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Business & tax" />
                    <CardBody>
                        <DescriptionList
                            items={[
                                { label: 'Organization name', value: profile.organization_name },
                                { label: 'Business type', value: profile.business_type },
                                { label: 'VAT / TIN', value: profile.vat_tin },
                                { label: 'Status', value: profile.status_label },
                            ]}
                        />
                    </CardBody>
                </Card>

                <Card>
                    <CardHeader title="Record" />
                    <CardBody>
                        <DescriptionList
                            items={[
                                { label: 'Created', value: formatDateTime(profile.created_at) },
                                { label: 'Last updated', value: formatDateTime(profile.updated_at) },
                            ]}
                        />
                    </CardBody>
                </Card>
            </div>
        </div>
    );
}

import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Drawer, Button, CloseButton } from '@chakra-ui/react';
import changelogRaw from '!!raw-loader!../../../CHANGELOG.md';
import { version } from '../../../package.json';
import './styles.scss';

const TAG_LABELS = {
	Added: __('Feature', 'easy-content-manager'),
	Changed: __('Improvement', 'easy-content-manager'),
	Fixed: __('Fix', 'easy-content-manager'),
	Security: __('Security', 'easy-content-manager'),
	Removed: __('Removed', 'easy-content-manager'),
};

function parseChangelogAge(dateString) {
	if (!dateString) return '';

	const diffDays = Math.floor(
		(Date.now() - new Date(dateString).getTime()) / 86400000
	);

	if (diffDays <= 0) return __('today', 'easy-content-manager');
	if (diffDays === 1) return __('1 day ago', 'easy-content-manager');
	if (diffDays < 7)
		return `${diffDays} ${__('days ago', 'easy-content-manager')}`;
	if (diffDays < 14) return __('1 week ago', 'easy-content-manager');
	if (diffDays < 30)
		return `${Math.floor(diffDays / 7)} ${__('weeks ago', 'easy-content-manager')}`;
	if (diffDays < 60) return __('1 month ago', 'easy-content-manager');
	return `${Math.floor(diffDays / 30)} ${__('months ago', 'easy-content-manager')}`;
}

function parseLatestRelease(raw) {
	const versionHeadingRegex = /^##\s+\[([\d.]+)]\s+—\s+(\d{4}-\d{2}-\d{2})\s*$/m;
	const match = versionHeadingRegex.exec(raw);
	if (!match) return null;

	const [fullMatch, releaseVersion, date] = match;
	const rest = raw.slice(match.index + fullMatch.length);
	const nextHeadingIndex = rest.search(/^##\s+\[/m);
	const body = nextHeadingIndex === -1 ? rest : rest.slice(0, nextHeadingIndex);

	const sections = [];
	let current = null;
	body.split('\n').forEach((line) => {
		const sectionMatch = line.match(/^###\s+(.+)/);
		const itemMatch = line.match(/^-\s+(.+)/);
		if (sectionMatch) {
			current = { label: sectionMatch[1].trim(), items: [] };
			sections.push(current);
		} else if (itemMatch && current) {
			current.items.push(itemMatch[1].trim());
		}
	});

	return { version: releaseVersion, date, sections };
}

function SectionTag({ label }) {
	const tagLabel = TAG_LABELS[label] || label;
	const tagClass = `ecm-whats-new__tag ecm-whats-new__tag--${label.toLowerCase()}`;
	return <span className={tagClass}>{tagLabel}</span>;
}

export default function WhatsNew() {
	const [open, setOpen] = useState(false);
	const release = parseLatestRelease(changelogRaw);

	if (!release) return null;

	const age = parseChangelogAge(release.date);

	return (
		<>
			<Button
				bg="transparent"
				borderWidth="1px"
				borderColor="var(--ecm-border-color)"
				color="var(--ecm-font-color)"
				padding="5px 12px"
				onClick={() => setOpen(true)}
			>
				<span className="easy-content-manager-icon easy-content-manager-icon--announcement" />
				{__("What's New", 'easy-content-manager')}
			</Button>

			<Drawer.Root
				open={open}
				onOpenChange={(e) => setOpen(e.open)}
				placement="end"
				size="sm"
			>
				<Drawer.Backdrop />
				<Drawer.Positioner>
					<Drawer.Content className="ecm-whats-new">
						<Drawer.Header className="ecm-whats-new__header">
							<Drawer.Title color="var(--ecm-font-color)">
								{__("What's New", 'easy-content-manager')}
							</Drawer.Title>
							<Drawer.CloseTrigger asChild position="static">
								<CloseButton size="sm" />
							</Drawer.CloseTrigger>
						</Drawer.Header>
						<Drawer.Body className="ecm-whats-new__body">
							<div className="ecm-whats-new__meta">
								{age && (
									<>
										<span className="ecm-whats-new__age">
											{age}
										</span>
										<span className="ecm-whats-new__dot" />
									</>
								)}
								<span className="ecm-whats-new__version">
									v{version}
								</span>
							</div>

							{release.sections.map((section, si) => (
								<div
									className="ecm-whats-new__section"
									key={si}
								>
									<div className="ecm-whats-new__section-label">
										{section.label}
									</div>
									<ul className="ecm-whats-new__items">
										{section.items.map((item, ii) => (
											<li
												className="ecm-whats-new__item"
												key={ii}
											>
												<SectionTag
													label={section.label}
												/>
												{item}
											</li>
										))}
									</ul>
								</div>
							))}
						</Drawer.Body>
					</Drawer.Content>
				</Drawer.Positioner>
			</Drawer.Root>
		</>
	);
}

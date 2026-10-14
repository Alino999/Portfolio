<?php

namespace Database\Seeders;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Certification;
use App\Models\Service;
use App\Models\Language;
use App\Models\ProfessionalReference;
use App\Models\Profile;
use App\Models\Section;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlainGuigmaCvSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'guigmaa507@gmail.com')->firstOrFail();
        $profile = Profile::updateOrCreate(['user_id' => $user->id], [
            'first_name' => 'Alain',
            'last_name' => 'GUIGMA',
            'title' => 'Maintenance informatique & cybersécurité',
            'title_en' => 'IT Maintenance & Cybersecurity',
            'subtitle' => 'Consultant IT · développement web et mobile · Sage X3 · cybersécurité',
            'subtitle_en' => 'IT consultant · web and mobile development · Sage X3 · cybersecurity',
            'summary' => 'Passionné par la sécurité informatique et la programmation web et mobile, je suis créatif, curieux, rigoureux, déterminé et sociable. Autodidacte, je suis immédiatement disponible.',
            'summary_en' => 'Passionate about information security and web and mobile development, I am creative, curious, rigorous, determined, and sociable. A self-taught professional, I am immediately available.',
            'bio' => 'Passionné par la sécurité informatique et la programmation web et mobile, je suis créatif, curieux, rigoureux, déterminé et sociable. Autodidacte, je suis immédiatement disponible.',
            'bio_en' => 'Passionate about information security and web and mobile development, I am creative, curious, rigorous, determined, and sociable. A self-taught professional, I am immediately available.',
            'email' => 'guigmaa507@gmail.com',
            'phone' => '+226 66 41 07 68 / 71 40 62 88',
            'location' => 'Songnaaba, Secteur 29, Ouagadougou',
            'country' => 'Burkina Faso',
            'availability' => 'Immédiatement disponible',
            'professional_status' => 'Consultant en maintenance informatique et cybersécurité',
            'years_experience' => 0,
            'avatar_path' => 'homepage-slides/iga338UKpezRDMo1qqsuo1YSZT0l8qifXIIUr39Z.png',
            'primary_skills' => ['Sage X3', 'Cybersécurité', 'Maintenance informatique'],
            'is_public' => true,
        ]);

        foreach ([
            ['platform' => 'GitHub', 'label' => 'GitHub', 'url' => 'https://github.com/Alino999', 'icon' => 'github'],
            ['platform' => 'LinkedIn', 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/alain-guigma-841758205', 'icon' => 'linkedin'],
            ['platform' => 'Facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/alain.guigma.90/', 'icon' => 'facebook'],
        ] as $order => $socialLink) {
            SocialLink::updateOrCreate(
                ['profile_id' => $profile->id, 'platform' => $socialLink['platform']],
                $socialLink + ['is_active' => true, 'sort_order' => $order + 1]
            );
        }

        foreach ([
            ['key' => 'about', 'title' => ['fr' => 'À propos', 'en' => 'About'], 'sort_order' => 1],
            ['key' => 'skills', 'title' => ['fr' => 'Compétences', 'en' => 'Skills'], 'sort_order' => 2],
            ['key' => 'experience', 'title' => ['fr' => 'Expérience', 'en' => 'Experience'], 'sort_order' => 3],
            ['key' => 'services', 'title' => ['fr' => 'Services', 'en' => 'Services'], 'sort_order' => 4],
            ['key' => 'certifications', 'title' => ['fr' => 'Certifications', 'en' => 'Certifications'], 'sort_order' => 5],
        ] as $section) {
            Section::updateOrCreate(['key' => $section['key']], $section + ['enabled' => true]);
        }

        foreach ([
            [
                'title' => ['fr' => 'Parcours Analyste junior en cybersécurité', 'en' => 'Junior Cybersecurity Analyst Career Path'],
                'description' => [
                    'fr' => 'Certificat de réussite du parcours. Compétences : recommandation de contrôles de cybersécurité, atténuation des menaces réseau et systèmes, évaluation des vulnérabilités et des risques, et contribution à un plan de réponse aux incidents.',
                    'en' => 'Certificate of completion. Skills include recommending cybersecurity controls, mitigating network and system threats, assessing vulnerabilities and risks, and contributing to an incident response plan.',
                ],
                'sort_order' => 1,
            ],
            [
                'title' => ['fr' => 'Gestion des cybermenaces', 'en' => 'Cyber Threat Management'],
                'description' => [
                    'fr' => 'Certificat de fin de formation. Compétences : conformité aux référentiels selon le contexte, évaluation des vulnérabilités réseau et systèmes, analyse des menaces, choix de contrôles de sécurité et gestion des risques, reprise après incident et réponse aux cyberattaques.',
                    'en' => 'Certificate of course completion. Skills include applying relevant compliance frameworks, assessing network and systems vulnerabilities, identifying threats, selecting security controls, managing risk, and recommending recovery and incident response activities.',
                ],
                'sort_order' => 2,
            ],
            [
                'title' => ['fr' => 'Attestation de réussite – Analyste en cybersécurité', 'en' => 'Cybersecurity Analyst Certificate of Achievement'],
                'issuer' => 'FORCE-N',
                'issuer_en' => 'FORCE-N',
                'issue_date' => '2025-05-01',
                'description' => [
                    'fr' => 'Attestation de réussite du certificat d’Analyste en cybersécurité délivrée par le programme FORCE-N en mai 2025.',
                    'en' => 'Certificate of achievement for the Cybersecurity Analyst program, issued by FORCE-N in May 2025.',
                ],
                'sort_order' => 3,
            ],
        ] as $certificationData) {
            $certification = Certification::query()
                ->where('title->en', $certificationData['title']['en'])
                ->first() ?? new Certification();

            $certification->fill($certificationData + [
                'issuer' => 'Cisco Networking Academy',
                'issuer_en' => 'Cisco Networking Academy',
                'issue_date' => '2024-12-09',
                'published' => true,
            ])->save();
        }
                $services = [
                    [
                        'title' => ['fr' => 'Conseil & Intégration Sage X3', 'en' => 'Sage X3 Consulting & Integration'],
                        'description' => ['fr' => 'Accompagnement dans le paramétrage, la configuration, le déploiement et l’optimisation de Sage X3 pour améliorer les processus métiers et la gestion de l’entreprise.', 'en' => 'Consulting, configuration, deployment and optimization of Sage X3 to improve business processes and enterprise management.'],
                        'icon' => '🧩',
                    ],
                    [
                        'title' => ['fr' => 'Paramétrage & Personnalisation Sage X3', 'en' => 'Sage X3 Configuration & Customization'],
                        'description' => ['fr' => 'Paramétrage des modules Sage X3, adaptation aux besoins métiers, création de workflows, gestion des données et personnalisation des processus.', 'en' => 'Configuration of Sage X3 modules, business process customization, workflow setup, data management and process optimization.'],
                        'icon' => '⚙️',
                    ],
                    [
                        'title' => ['fr' => 'Support & Assistance Informatique', 'en' => 'IT Support & Assistance'],
                        'description' => ['fr' => 'Diagnostic et résolution des problèmes informatiques, assistance aux utilisateurs, installation de logiciels, configuration des postes et accompagnement technique.', 'en' => 'IT troubleshooting, user assistance, software installation, workstation configuration and technical support.'],
                        'icon' => '🖥️',
                    ],
                    [
                        'title' => ['fr' => 'Maintenance & Dépannage Informatique', 'en' => 'IT Maintenance & Troubleshooting'],
                        'description' => ['fr' => 'Maintenance préventive et corrective des ordinateurs, réseaux et équipements informatiques afin de garantir leur disponibilité et leurs performances.', 'en' => 'Preventive and corrective maintenance of computers, networks and IT equipment to ensure reliability, availability and performance.'],
                        'icon' => '🛠️',
                    ],
                    [
                        'title' => ['fr' => 'Cybersécurité & Sécurité des Systèmes', 'en' => 'Cybersecurity & System Security'],
                        'description' => ['fr' => 'Évaluation des risques, sécurisation des systèmes, contrôle des accès, bonnes pratiques de sécurité et accompagnement dans la protection des données.', 'en' => 'Risk assessment, system hardening, access control, security best practices and support for protecting information and systems.'],
                        'icon' => '🛡️',
                    ],
                    [
                        'title' => ['fr' => 'Audit & Évaluation de Sécurité', 'en' => 'Security Audit & Assessment'],
                        'description' => ['fr' => 'Analyse de l’environnement informatique pour identifier les vulnérabilités, les risques et les axes d’amélioration de la sécurité.', 'en' => 'Assessment of IT environments to identify vulnerabilities, security risks and opportunities for improvement.'],
                        'icon' => '🔎',
                    ],
                    [
                        'title' => ['fr' => 'Administration Systèmes & Réseaux', 'en' => 'Systems & Network Administration'],
                        'description' => ['fr' => 'Installation, configuration et administration des systèmes, serveurs et réseaux afin d’assurer leur disponibilité, leur performance et leur sécurité.', 'en' => 'Installation, configuration and administration of systems, servers and networks to ensure availability, performance and security.'],
                        'icon' => '🌐',
                    ],
                    [
                        'title' => ['fr' => 'Développement d’Applications Web', 'en' => 'Web Application Development'],
                        'description' => ['fr' => 'Conception et développement d’applications web modernes, dynamiques et adaptées aux besoins spécifiques des entreprises.', 'en' => 'Design and development of modern, dynamic web applications tailored to specific business requirements.'],
                        'icon' => '💻',
                    ],
                    [
                        'title' => ['fr' => 'Création de Sites Web', 'en' => 'Professional Website Development'],
                        'description' => ['fr' => 'Création de sites web modernes, responsives et professionnels pour entreprises, organisations et professionnels indépendants.', 'en' => 'Creation of modern, responsive and professional websites for businesses, organizations and independent professionals.'],
                        'icon' => '🌍',
                    ],
                    [
                        'title' => ['fr' => 'Développement Laravel', 'en' => 'Laravel Development'],
                        'description' => ['fr' => 'Développement d’applications web avec Laravel, intégrant des interfaces modernes, des bases de données, des systèmes d’authentification et des fonctionnalités métier.', 'en' => 'Development of web applications using Laravel, with modern interfaces, databases, authentication systems and business features.'],
                        'icon' => '⚡',
                    ],
                    [
                        'title' => ['fr' => 'Bases de Données & Sécurité des Données', 'en' => 'Database Management & Data Security'],
                        'description' => ['fr' => 'Conception, gestion, optimisation et sécurisation des bases de données pour garantir l’intégrité, la disponibilité et la confidentialité des informations.', 'en' => 'Design, management, optimization and security of databases to ensure data integrity, availability and confidentiality.'],
                        'icon' => '🗄️',
                    ],
                    [
                        'title' => ['fr' => 'Déploiement & Hébergement d’Applications', 'en' => 'Web Deployment & Hosting'],
                        'description' => ['fr' => 'Déploiement d’applications web et configuration des environnements d’hébergement, bases de données, domaines et services nécessaires à leur mise en production.', 'en' => 'Deployment of web applications and configuration of hosting environments, databases, domains and production services.'],
                        'icon' => '🚀',
                    ],
                    [
                        'title' => ['fr' => 'Sécurité des Applications Web', 'en' => 'Web Application Security'],
                        'description' => ['fr' => 'Analyse et amélioration de la sécurité des applications web afin de réduire les risques liés aux vulnérabilités, aux accès non autorisés et aux fuites de données.', 'en' => 'Assessment and improvement of web application security to reduce risks related to vulnerabilities, unauthorized access and data breaches.'],
                        'icon' => '🔐',
                    ],
                    [
                        'title' => ['fr' => 'Conseil en Transformation Digitale', 'en' => 'Digital Transformation Consulting'],
                        'description' => ['fr' => 'Accompagnement des organisations dans la digitalisation de leurs processus, l’adoption d’outils numériques et l’amélioration de leur efficacité opérationnelle.', 'en' => 'Helping organizations digitize their processes, adopt digital tools and improve operational efficiency.'],
                        'icon' => '📈',
                    ],
                    [
                        'title' => ['fr' => 'Déploiement de Solutions Métiers', 'en' => 'Business Software Deployment & Support'],
                        'description' => ['fr' => 'Installation, configuration, assistance et accompagnement des utilisateurs lors du déploiement de solutions informatiques adaptées aux besoins de l’entreprise.', 'en' => 'Installation, configuration, user assistance and deployment support for business software solutions tailored to organizational needs.'],
                        'icon' => '📦',
                    ],
                    [
                        'title' => ['fr' => 'Formation & Accompagnement IT', 'en' => 'IT Training & User Support'],
                        'description' => ['fr' => 'Formation et accompagnement des utilisateurs sur les outils informatiques, les logiciels métiers, les bonnes pratiques de cybersécurité et les systèmes d’information.', 'en' => 'Training and guidance on IT tools, business software, cybersecurity best practices and information systems.'],
                        'icon' => '🎓',
                    ],
                ];

                foreach ($services as $order => $serviceData) {
                    $service = Service::query()
                        ->where('title->fr', $serviceData['title']['fr'])
                        ->first() ?? new Service();

                    $service->fill($serviceData + [
                        'sort_order' => $order + 1,
                        'published' => true,
                    ])->save();
                }

        $categories = [];
        foreach ([
            ['slug' => 'web-development', 'name' => ['fr' => 'Développement web', 'en' => 'Web development']],
            ['slug' => 'databases', 'name' => ['fr' => 'Bases de données', 'en' => 'Databases']],
            ['slug' => 'programming', 'name' => ['fr' => 'Programmation', 'en' => 'Programming']],
            ['slug' => 'systems', 'name' => ['fr' => 'Systèmes', 'en' => 'Systems']],
            ['slug' => 'maintenance', 'name' => ['fr' => 'Maintenance informatique', 'en' => 'IT maintenance']],
            ['slug' => 'devops', 'name' => ['fr' => 'DevOps', 'en' => 'DevOps']],
            ['slug' => 'design', 'name' => ['fr' => 'Design', 'en' => 'Design']],
            ['slug' => 'erp', 'name' => ['fr' => 'ERP', 'en' => 'ERP']],
            ['slug' => 'cybersecurity', 'name' => ['fr' => 'Cybersécurité', 'en' => 'Cybersecurity']],
        ] as $order => $category) {
            $categories[$category['slug']] = SkillCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'sort_order' => $order + 1, 'published' => true]
            );
        }

        $skills = [
            ['HTML/CSS / Bootstrap', 'HTML/CSS / Bootstrap', 'web-development'],
            ['JS / jQuery / Total.js', 'JavaScript / jQuery / Total.js', 'web-development'],
            ['PHP / Laravel', 'PHP / Laravel', 'web-development'],
            ['SQL / MySQL / PostgreSQL / Oracle', 'SQL / MySQL / PostgreSQL / Oracle', 'databases'],
            ['Java', 'Java', 'programming'],
            ['C#', 'C#', 'programming'],
            ['Windows', 'Windows', 'systems'],
            ['Windows Server', 'Windows Server', 'systems'],
            ['Linux', 'Linux', 'systems'],
            ['Maintenance informatique', 'IT maintenance', 'maintenance'],
            ['DevOps', 'DevOps', 'devops'],
            ['Photoshop / XD / Canva', 'Photoshop / XD / Canva', 'design'],
            ['Sage X3', 'Sage X3', 'erp'],
            ['Cybersécurité', 'Cybersecurity', 'cybersecurity'],
        ];

        foreach ($skills as $order => [$frenchName, $englishName, $categorySlug]) {
            $skill = Skill::query()->where('name->fr', $frenchName)->first() ?? new Skill();
            $skill->fill([
                'skill_category_id' => $categories[$categorySlug]->id,
                'name' => ['fr' => $frenchName, 'en' => $englishName],
                'level' => null,
                'level_en' => null,
                'percentage' => null,
                'years_experience' => 0,
                'description' => null,
                'sort_order' => $order + 1,
                'published' => true,
            ])->save();
        }

        foreach ([
            ['school' => 'Université Aube Nouvelle', 'degree' => 'Master', 'field' => 'Systèmes informatiques', 'start_date' => '2024-01-01', 'end_date' => '2026-12-31', 'sort_order' => 1],
            ['school' => 'FORCE-N', 'degree' => 'Formation en cybersécurité', 'field' => 'Cybersécurité', 'start_date' => '2024-01-01', 'end_date' => '2024-01-01', 'sort_order' => 2],
            ['school' => 'Université Aube Nouvelle', 'degree' => 'Licence', 'field' => 'Génie logiciel', 'start_date' => '2023-01-01', 'end_date' => '2024-12-31', 'sort_order' => 3],
        ] as $education) {
            Education::updateOrCreate(
                ['profile_id' => $profile->id, 'school' => $education['school'], 'degree' => $education['degree']],
                $education + ['published' => true]
            );
        }

        $experiences = [
            ['company' => 'GROUPE LOGICIEL & SERVICES', 'position' => 'Consultant Sage X3, MedSys et GMAO · Formateur · Requêteur SQL', 'position_en' => 'Sage X3, MedSys and CMMS consultant · Trainer · SQL query writer', 'start_date' => '2025-02-01', 'end_date' => null, 'is_current' => true, 'responsibilities' => ['fr' => ['Conseil sur Sage X3, MedSys et GMAO', 'Formation', 'Requêtes SQL'], 'en' => ['Consulting on Sage X3, MedSys, and CMMS', 'Training', 'SQL queries']], 'technologies' => ['Sage X3', 'MedSys', 'GMAO', 'SQL'], 'sort_order' => 1],
            ['company' => 'DJAGO INTERNATIONAL', 'position' => 'Consultant Sage X3, MedSys et GMAO · Formateur', 'position_en' => 'Sage X3, MedSys and CMMS consultant · Trainer', 'start_date' => '2024-07-01', 'end_date' => '2025-02-28', 'is_current' => false, 'responsibilities' => ['fr' => ['Conseil sur Sage X3, MedSys et GMAO', 'Formation'], 'en' => ['Consulting on Sage X3, MedSys, and CMMS', 'Training']], 'technologies' => ['Sage X3', 'MedSys', 'GMAO'], 'sort_order' => 2],
            ['company' => 'Total Innovation', 'position' => 'Technicien · Développeur web et mobile · Web designer · Formateur en maintenance informatique', 'position_en' => 'Technician · Web and mobile developer · Web designer · IT maintenance trainer', 'start_date' => '2023-01-01', 'end_date' => '2024-06-30', 'is_current' => false, 'responsibilities' => ['fr' => ['Développement web et mobile', 'Web design', 'Formation en maintenance informatique'], 'en' => ['Web and mobile development', 'Web design', 'IT maintenance training']], 'technologies' => ['Web', 'Mobile', 'Maintenance informatique'], 'sort_order' => 3],
            ['company' => 'DAAN (ASECNA BF)', 'position' => 'Stagiaire en informatique', 'position_en' => 'IT intern', 'start_date' => '2023-01-01', 'end_date' => '2023-01-01', 'is_current' => false, 'contract_type' => 'Stage de 4 mois', 'responsibilities' => ['fr' => ['Gestion et maintenance du parc informatique de la Direction et de l’Aéroport international de Ouagadougou', 'Stage de quatre mois en 2023'], 'en' => ['Managed and maintained IT equipment for the Directorate and Ouagadougou International Airport', 'Four-month internship in 2023']], 'technologies' => ['Maintenance informatique', 'Windows'], 'sort_order' => 4],
            ['company' => 'Afric Consultin Group', 'position' => 'Développeur web et technicien', 'position_en' => 'Web developer and IT technician', 'start_date' => '2022-01-01', 'end_date' => '2022-01-01', 'is_current' => false, 'responsibilities' => ['fr' => ['Développement web et support technique durant l’année 2022'], 'en' => ['Web development and technical support during 2022']], 'technologies' => ['Web', 'Maintenance informatique'], 'sort_order' => 5],
            ['company' => 'Afric Consultin Group', 'position' => 'Stagiaire en développement web et technicien', 'position_en' => 'Web development intern and IT technician', 'start_date' => '2021-07-05', 'end_date' => '2022-01-04', 'is_current' => false, 'contract_type' => 'Stage de 6 mois', 'responsibilities' => ['fr' => ['Stage de six mois, du 5 juillet 2021 au 4 janvier 2022', 'Développement web et support technique'], 'en' => ['Six-month internship, July 5, 2021 to January 4, 2022', 'Web development and technical support']], 'technologies' => ['Web', 'Maintenance informatique'], 'sort_order' => 6],
        ];

        foreach ($experiences as $experience) {
            Experience::updateOrCreate(
                ['profile_id' => $profile->id, 'company' => $experience['company'], 'start_date' => $experience['start_date']],
                $experience + ['published' => true]
            );
        }

        foreach ([
            ['name' => ['fr' => 'Moré', 'en' => 'Mooré'], 'locale' => 'mos'],
            ['name' => ['fr' => 'Français', 'en' => 'French'], 'locale' => 'fr'],
            ['name' => ['fr' => 'Anglais', 'en' => 'English'], 'locale' => 'en'],
        ] as $order => $language) {
            Language::updateOrCreate(
                ['locale' => $language['locale']],
                ['name' => $language['name'], 'level' => null, 'sort_order' => $order + 1, 'published' => true]
            );
        }

        foreach ([
            ['name' => 'Louis NABALOUM', 'role' => 'Développeur', 'company' => 'Total Avengers', 'phone' => '+226 56 92 06 71'],
            ['name' => 'Ïmael OUIYA', 'role' => 'Ingénieur Cloud', 'company' => 'TI', 'phone' => '+226 60 18 54 25'],
            ['name' => 'Fatah Oumar OUEDRAOGO', 'role' => 'Informaticien', 'company' => 'Burkina Mining Company', 'phone' => '+226 70 19 50 32'],
        ] as $order => $reference) {
            ProfessionalReference::updateOrCreate(
                ['profile_id' => $profile->id, 'name' => $reference['name']],
                $reference + ['sort_order' => $order + 1, 'published' => true]
            );
        }
    }
}